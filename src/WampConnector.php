<?php

namespace Depotwarehouse\Jeopardy;

use Depotwarehouse\Jeopardy\Board\Category;
use Depotwarehouse\Jeopardy\Board\Question;
use Depotwarehouse\Jeopardy\Board\QuestionDisplayRequestEvent;
use Depotwarehouse\Jeopardy\Board\QuestionSubscriptionEvent;
use Depotwarehouse\Jeopardy\Buzzer\BuzzerResolution;
use Depotwarehouse\Jeopardy\Buzzer\BuzzerStatus;
use Depotwarehouse\Jeopardy\Buzzer\BuzzerStatusChangeEvent;
use Depotwarehouse\Jeopardy\Buzzer\BuzzerStatusSubscriptionEvent;
use Depotwarehouse\Jeopardy\Buzzer\BuzzReceivedEvent;
use Depotwarehouse\Jeopardy\Participant\Contestant;
use Depotwarehouse\Jeopardy\Participant\ContestantScoreSubscriptionEvent;
use Illuminate\Support\Collection;
use League\Event\Emitter;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Ratchet\Wamp\WampServerInterface;

class WampConnector implements WampServerInterface
{

    const BUZZER_TOPIC = "com.sc2ctl.jeopardy.buzzer";
    const BUZZER_STATUS_TOPIC = "com.sc2ctl.jeopardy.buzzer_status";
    const QUESTION_DISPLAY_TOPIC = "com.sc2ctl.jeopardy.question_display";
    const QUESTION_DISMISS_TOPIC = "com.sc2ctl.jeopardy.question_dismiss";
    const QUESTION_ANSWER_QUESTION = "com.sc2ctl.jeopardy.question_answer";
    const CONTESTANT_SCORE = "com.sc2ctl.jeopardy.contestant_score";
    const DAILY_DOUBLE_BET_TOPIC = "com.sc2ctl.jeopardy.daily_double_bet";

    public function __construct(Emitter $emitter)
    {
        $this->emitter = $emitter;
    }

    /** @var  Emitter */
    protected $emitter;

    /** @var Topic[] */
    protected $subscribedTopics = [ ];

    /**
     * When a new connection is opened it will be passed to this method
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     * @throws \Exception
     */
    function onOpen(ConnectionInterface $conn)
    {
        // TODO: Implement onOpen() method.
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     * @throws \Exception
     */
    function onClose(ConnectionInterface $conn)
    {
        // TODO: Implement onClose() method.
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param  ConnectionInterface $conn
     * @param  \Exception $e
     * @throws \Exception
     */
    function onError(ConnectionInterface $conn, \Exception $e)
    {
        // TODO: Implement onError() method.
    }

    /**
     * An RPC call has been received
     * @param \Ratchet\ConnectionInterface $conn
     * @param string $id The unique ID of the RPC, required to respond to
     * @param string|Topic $topic The topic to execute the call against
     * @param array $params Call parameters received from the client
     */
    function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        $conn->send("You called!");
    }

    /**
     * A request to subscribe to a topic has been made
     * @param \Ratchet\ConnectionInterface $conn
     * @param string|Topic $topic The topic to subscribe to
     */
    function onSubscribe(ConnectionInterface $conn, $topic)
    {
        $this->subscribedTopics[$topic->getId()] = $topic;

        switch ((string)$topic) {
            case self::QUESTION_DISPLAY_TOPIC:
                $this->emitter->emit(new QuestionSubscriptionEvent($this->getSessionIdFromConnection($conn)));
                break;
            case self::BUZZER_STATUS_TOPIC:
                $this->emitter->emit(new BuzzerStatusSubscriptionEvent($this->getSessionIdFromConnection($conn)));
                break;
            case self::CONTESTANT_SCORE:
                $this->emitter->emit(new ContestantScoreSubscriptionEvent($this->getSessionIdFromConnection($conn)));
                break;
            default:
                break;
        }

    }

    /**
     * A request to unsubscribe from a topic has been made
     * @param \Ratchet\ConnectionInterface $conn
     * @param string|Topic $topic The topic to unsubscribe from
     */
    function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        // TODO: Implement onUnSubscribe() method.
    }

    /**
     * A client is attempting to publish content to a subscribed connections on a URI
     * @param \Ratchet\ConnectionInterface $conn
     * @param string|Topic $topic The topic the user has attempted to publish to
     * @param string $event Payload of the publish
     * @param array $exclude A list of session IDs the message should be excluded from (blacklist)
     * @param array $eligible A list of session Ids the message should be send to (whitelist)
     */
    function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {

        switch ((string)$topic) {
            case self::BUZZER_TOPIC:
                $contestant = new Contestant($event['name']);
                $this->emitter->emit(new BuzzReceivedEvent($contestant, $event['difference']));
                break;

            case self::BUZZER_STATUS_TOPIC:
                $this->emitter->emit(
                    new BuzzerStatusChangeEvent(
                        new BuzzerStatus($event['active'])
                    )
                );
                break;

            case self::QUESTION_DISPLAY_TOPIC:
                if (!isset($event['category']) || !isset($event['value'])) {
                    //TODO log this
                    echo "Did not receive proper question request, did not have a category or value\n";
                    break;
                }
                $this->emitter->emit(new QuestionDisplayRequestEvent($event['category'], $event['value']));
                break;

            case self::QUESTION_ANSWER_QUESTION:
                if (!isset($event['category']) || !isset($event['value']) || !isset($event['contestant'])) {
                    //TODO log this
                    echo "Did not receive proper question answer request, did not have a category or value or playerName\n";
                    break;
                }

                $questionAnswerEvent = new Question\QuestionAnswerEvent(
                    new Question\QuestionAnswer(
                        $event['category'],
                        $event['value'],
                        new Contestant($event['contestant']),
                        (isset($event['correct'])) ? $event['correct'] : false
                    )
                );

                if (isset ($event['bet'])) {
                    $questionAnswerEvent->getQuestionAnswer()->setBet($event['bet']);
                }

                $this->emitter->emit($questionAnswerEvent);

                break;

            case self::QUESTION_DISMISS_TOPIC:
                if (!isset($event['category']) || !isset($event['value'])) {
                    //TODO log this
                    echo "Did not receive proper dismiss request, did not have a category or value\n";
                    break;
                }
                $dismissal = new Question\QuestionDismissalEvent(
                    new Question\QuestionDismissal(
                        $event['category'],
                        $event['value']
                    )
                );

                $this->emitter->emit($dismissal);
                break;

            case self::DAILY_DOUBLE_BET_TOPIC:
                if (!isset($event['category']) || !isset($event['value']) || !isset($event['bet'])) {
                    //TODO logging
                    echo "Recieved invalid daily double bet\n";
                    break;
                }

                $this->emitter->emit(
                    new Question\DailyDouble\DailyDoubleBetEvent(
                        $event['value'],
                        $event['category'],
                        $event['bet']
                    )
                );
                break;

            default:
                break;
        }

    }

    private function getSessionIdFromConnection(ConnectionInterface $conn)
    {
        //TODO this doesn't seem safe at all. Maybe a pull request?
        // TODO https://github.com/ratchetphp/Ratchet/issues/282
        return $conn->wrappedConn->WAMP->sessionId;
    }

    /**
     * We have resolved who buzzed in first, notify the relevant entities.
     *
     * @param BuzzerResolution $resolution
     */
    public function onBuzzerResolution(BuzzerResolution $resolution)
    {
        // Is anyone subscribed to buzzer events right now?
        if (!array_key_exists(self::BUZZER_TOPIC, $this->subscribedTopics)) {
            return;
        }

        $this->subscribedTopics[self::BUZZER_TOPIC]->broadcast($resolution->toJson());
    }

    public function onBuzzerStatusChange(BuzzerStatus $status, $blacklist = [ ], $whitelist = [ ])
    {
        $this->subscribedTopics[self::BUZZER_STATUS_TOPIC]->broadcast($status->toJson(), $blacklist, $whitelist);
    }

    /**
     * When a user subscribes to the Contestant Score feed, they should get and update of all the current contestants
     * @param Collection $contestants
     * @param string $sessionId The session ID of the user who subscribed.
     */
    public function onContestantScoreSubscription(Collection $contestants, $sessionId)
    {
        $response = $contestants->map(function (Contestant $contestant) {
            return $contestant->toArray();
        })->toJson();

        $this->subscribedTopics[self::CONTESTANT_SCORE]->broadcast($response, [ ], [ $sessionId ]);
    }

    /**
     * We have a new subscriber to the question feed, which means we want to send them the data about all the currently
     * active questions.
     * @param Collection $categories A collection which contains Category objects.
     * @param string $sessionId The session ID of the user who subscribed.
     */
    public function onQuestionSubscribe(Collection $categories, $sessionId)
    {
        $response = $categories->map(function (Category $category) {
            return $category->toArray();
        });

        $this->subscribedTopics[self::QUESTION_DISPLAY_TOPIC]->broadcast(json_encode($response), [ ], [ $sessionId ]);
    }

    public function onQuestionDisplay(Question $question, $category)
    {
        $response = $question->toArray();
        $response['category'] = $category;
        $response = json_encode($response);

        $this->subscribedTopics[self::QUESTION_DISPLAY_TOPIC]->broadcast($response);
    }

    public function onQuestionAnswer(Question\QuestionAnswer $questionAnswer)
    {
        $response = $questionAnswer->toArray();
        $response = json_encode($response);

        $this->subscribedTopics[self::QUESTION_ANSWER_QUESTION]->broadcast($response);
    }

    public function onDailyDoubleBetRecieved(Question $question, $category, $bet)
    {
        $response = $question->toArray();
        $response['category'] = $category;
        $response['bet'] = $bet;
        $response = json_encode($response);

        $this->subscribedTopics[self::DAILY_DOUBLE_BET_TOPIC]->broadcast($response);
    }

    public function onQuestionDismiss(Question\QuestionDismissal $dismissal)
    {
        $response = $dismissal->toJson();
        $this->subscribedTopics[self::QUESTION_DISMISS_TOPIC]->broadcast($response);
    }

}
