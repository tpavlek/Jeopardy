<?php

namespace Depotwarehouse\Jeopardy;

use Depotwarehouse\Jeopardy\Board\BoardFactory;
use Depotwarehouse\Jeopardy\Board\Question\DailyDouble\DailyDoubleBetEvent;
use Depotwarehouse\Jeopardy\Board\Question\FinalJeopardy\FinalJeopardyAnswerEvent;
use Depotwarehouse\Jeopardy\Board\Question\FinalJeopardy\FinalJeopardyAnswerRequest;
use Depotwarehouse\Jeopardy\Board\Question\FinalJeopardy\FinalJeopardyBetEvent;
use Depotwarehouse\Jeopardy\Board\Question\FinalJeopardy\FinalJeopardyCategoryRequest;
use Depotwarehouse\Jeopardy\Board\Question\FinalJeopardy\FinalJeopardyClueRequest;
use Depotwarehouse\Jeopardy\Board\Question\FinalJeopardy\FinalJeopardyQuestionResponse;
use Depotwarehouse\Jeopardy\Board\Question\FinalJeopardy\FinalJeopardyResponseRequest;
use Depotwarehouse\Jeopardy\Board\Question\QuestionAnswer;
use Depotwarehouse\Jeopardy\Board\Question\QuestionAnswerEvent;
use Depotwarehouse\Jeopardy\Board\Question\QuestionDismissal;
use Depotwarehouse\Jeopardy\Board\Question\QuestionDismissalEvent;
use Depotwarehouse\Jeopardy\Board\QuestionDisplayRequestEvent;
use Depotwarehouse\Jeopardy\Board\QuestionNotFoundException;
use Depotwarehouse\Jeopardy\Board\QuestionSubscriptionEvent;
use Depotwarehouse\Jeopardy\Buzzer\BuzzerResolution;
use Depotwarehouse\Jeopardy\Buzzer\BuzzerResolutionEvent;
use Depotwarehouse\Jeopardy\Buzzer\BuzzerStatus;
use Depotwarehouse\Jeopardy\Buzzer\BuzzerStatusChangeEvent;
use Depotwarehouse\Jeopardy\Buzzer\BuzzerStatusSubscriptionEvent;
use Depotwarehouse\Jeopardy\Buzzer\BuzzReceivedEvent;
use Depotwarehouse\Jeopardy\Buzzer\Resolver;
use Depotwarehouse\Jeopardy\Participant\Contestant;
use Depotwarehouse\Jeopardy\Participant\ContestantScoreSubscriptionEvent;
use League\Event\Emitter;
use League\Event\Event;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\Wamp\WampServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

class Server
{

    const SOCKET_LISTEN_PORT = 9001;

    /** @var  LoopInterface */
    protected $eventLoop;

    /**
     * The time, in seconds, in which we want to wait after receiving the first buzz before resolving a winner.
     * @var float
     */
    protected $buzzer_resolve_timeout = 0.5;

    /**
     * The time, in seconds that we will wait for final jeopardy bets/responses to come in.
     * @var int
     */
    protected $final_jeopardy_collection_timeout = 5.5;

    public function __construct(LoopInterface $loopInterface)
    {
        $this->eventLoop = $loopInterface;
    }

    public function run(BoardFactory $boardFactory)
    {
        $emitter = new Emitter();
        $wamp = new WampConnector($emitter);

        $webSocket = new \React\Socket\Server($this->eventLoop);
        $webSocket->listen(self::SOCKET_LISTEN_PORT, '0.0.0.0');

        $board = $boardFactory->initialize();

        $webServer = new IoServer(
            new HttpServer(
                new WsServer(
                    new WampServer(
                        $wamp
                    )
                )
            ),
            $webSocket
        );

        $emitter->addListener(QuestionSubscriptionEvent::class, function(QuestionSubscriptionEvent $event) use ($wamp, $board) {
            $wamp->onQuestionSubscribe($board->getCategories(), $event->getSessionId());
        });

        $emitter->addListener(ContestantScoreSubscriptionEvent::class, function(ContestantScoreSubscriptionEvent $event) use ($wamp, $board) {
            $wamp->onContestantScoreSubscription($board->getContestants(), $event->getSessionId());
        });

        $emitter->addListener(BuzzerStatusSubscriptionEvent::class, function(BuzzerStatusSubscriptionEvent $event) use ($wamp, $board) {
            $wamp->onBuzzerStatusChange($board->getBuzzerStatus(), [], [ $event->getSessionId() ]);
        });

        $emitter->addListener(BuzzerResolutionEvent::class, function(BuzzerResolutionEvent $event) use ($wamp) {
            $wamp->onBuzzerResolution($event->getResolution());
        });

        $emitter->addListener(QuestionAnswerEvent::class, function(QuestionAnswerEvent $event) use ($wamp, $board, $emitter) {
            $questionAnswer = $event->getQuestionAnswer();
            $board->addScore($questionAnswer->getContestant(), $questionAnswer->getRealValue());
            $wamp->onQuestionAnswer($questionAnswer);

            if ($questionAnswer->isCorrect()) {
                $emitter->emit(new QuestionDismissalEvent(new QuestionDismissal($questionAnswer->getCategory(), $questionAnswer->getValue())));
                return;
            }

            $emitter->emit(new BuzzerStatusChangeEvent(new BuzzerStatus(true)));
        });

        $emitter->addListener(QuestionDisplayRequestEvent::class, function(QuestionDisplayRequestEvent $event) use ($wamp, $board) {
            try {
                $question = $board->getQuestionByCategoryAndValue($event->getCategoryName(), $event->getValue());
                $question->setUsed();
                $wamp->onQuestionDisplay($question, $event->getCategoryName());
            } catch (QuestionNotFoundException $exception) {
                //TODO log this somewhere.
                echo "Error occured, could not find question in category: {$event->getCategoryName()} valued at: {$event->getValue()}";
            }

        });

        $emitter->addListener(DailyDoubleBetEvent::class, function(DailyDoubleBetEvent $event) use ($wamp, $board) {
            $question = $board->getQuestionByCategoryAndValue($event->getCategory(), $event->getValue());
            $wamp->onDailyDoubleBetRecieved($question, $event->getCategory(), $event->getBet());
        });

        $emitter->addListener(QuestionDismissalEvent::class, function(QuestionDismissalEvent $event) use ($wamp, $board) {
            $dismissal = $event->getDismissal();
            $wamp->onQuestionDismiss($dismissal);

        });

        $emitter->addListener(BuzzReceivedEvent::class, function(BuzzReceivedEvent $event) use ($board, $emitter) {
            if (!$board->getBuzzerStatus()->isActive()) {
                // The buzzer isn't active, so there's nothing to do.
                return;
            }
            if ($board->getResolver()->isEmpty()) {
                // If this is the first buzz, then we want to resolve it after the timeout.
                $this->eventLoop->addTimer($this->buzzer_resolve_timeout, function() use ($board, $emitter) {
                    $resolution = $board->resolveBuzzes();
                    $emitter->emit(new BuzzerResolutionEvent($resolution));
                });
            }

            $board->getResolver()->addBuzz($event);

        });

        $emitter->addListener(BuzzerStatusChangeEvent::class, function(BuzzerStatusChangeEvent $event) use ($wamp, $board) {
            $board->setBuzzerStatus($event->getBuzzerStatus());
            $wamp->onBuzzerStatusChange($event->getBuzzerStatus());
        });

        $emitter->addListener(FinalJeopardyCategoryRequest::class, function(FinalJeopardyCategoryRequest $event) use ($wamp, $board) {
            $wamp->onFinalJeopardyRequest("category", $board->getFinalJeopardyClue());
        });

        $emitter->addListener(FinalJeopardyClueRequest::class, function(FinalJeopardyClueRequest $event) use ($wamp, $board) {
            $wamp->onFinalJeopardyBetCollectionRequest();

            // We're going to wait for a set time
            $this->eventLoop->addTimer($this->final_jeopardy_collection_timeout, function() use ($wamp, $board) {
                if (!$board->getFinalJeopardy()->hasAllBets()) {
                    //TODO logging
                    echo "Did not recieve all bets.\n";
                    $missingbets = $board->getFinalJeopardy()->getMissingBets();
                    $missingbets = implode(", ", $missingbets);
                    echo "Require bets from: {$missingbets}\n";
                }
                $wamp->onFinalJeopardyRequest("clue", $board->getFinalJeopardyClue());
            });



        });

        $emitter->addListener(FinalJeopardyBetEvent::class, function(FinalJeopardyBetEvent $event) use ($wamp, $board) {
            $finalJeopardy = $board->getFinalJeopardy();
            $finalJeopardy->setBet($event->getContestant(), $event->getBet());
        });

        $emitter->addListener(FinalJeopardyAnswerRequest::class, function(FinalJeopardyAnswerRequest $event) use ($wamp, $board) {
            $wamp->onFinalJeopardyAnswerCollectionRequest();

            $timer = $this->eventLoop->addTimer($this->final_jeopardy_collection_timeout, function() use ($wamp, $board) {
                if (!$board->getFinalJeopardy()->hasAllAnswers()) {
                    //TODO logging
                    echo "Did not receive all final jeopardy answers!\n";
                    $missingAnswers = $board->getFinalJeopardy()->getMissingAnswers();
                    $missingAnswers = implode(", ", $missingAnswers);
                    echo "Require answers from: {$missingAnswers}\n";
                }
                $wamp->onFinalJeopardyRequest("answer", $board->getFinalJeopardyClue());
            });



        });

        $emitter->addListener(FinalJeopardyAnswerEvent::class, function(FinalJeopardyAnswerEvent $event) use ($wamp, $board) {
            $finalJeopardy = $board->getFinalJeopardy();

            if ($finalJeopardy->hasAnswer($event->getContestant())) {
                //TODO logging
                echo "{$event->getContestant()} has already submitted a final answer";
                return;
            }

            $finalJeopardy->setAnswer($event->getContestant(), $event->getAnswer());
        });

        $emitter->addListener(FinalJeopardyResponseRequest::class, function(FinalJeopardyResponseRequest $event) use ($wamp, $board) {
            $finalJeopardy = $board->getFinalJeopardy();

            if (!$finalJeopardy->hasAnswer($event->getContestant())) {
                //TODO logging
                $response = new FinalJeopardyQuestionResponse($event->getContestant(), 0, "No answer, Troy");
                $wamp->onFinalJeopardyResponse($response);
                return;
            }

            $wamp->onFinalJeopardyResponse($finalJeopardy->getResponse($event->getContestant()));
        });

        $this->eventLoop->run();
    }

}
