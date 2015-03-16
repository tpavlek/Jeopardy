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

    // Our list of topics. These should all be replicated in the jeopardy.js module.
    const BUZZER_TOPIC = "com.sc2ctl.jeopardy.buzzer";
    const BUZZER_STATUS_TOPIC = "com.sc2ctl.jeopardy.buzzer_status";
    const QUESTION_DISPLAY_TOPIC = "com.sc2ctl.jeopardy.question_display";
    const QUESTION_DISMISS_TOPIC = "com.sc2ctl.jeopardy.question_dismiss";
    const QUESTION_ANSWER_QUESTION = "com.sc2ctl.jeopardy.question_answer";
    const CONTESTANT_SCORE = "com.sc2ctl.jeopardy.contestant_score";
    const DAILY_DOUBLE_BET_TOPIC = "com.sc2ctl.jeopardy.daily_double_bet";
    const FINAL_JEOPARDY_TOPIC = "com.sc2ctl.jeopardy.final_jeopardy";

    public function __construct(Emitter $emitter)
    {
        $this->emitter = $emitter;
    }

    /**
     * Our reference to the event Emitter so we can notify the rest of the system when events have occurred.
     *
     * @var Emitter
     */
    protected $emitter;

    /** @var Topic[] */
    protected $subscribedTopics = [ ];

    /**
     * A request to subscribe to a topic has been made.
     *
     * Usually we just want to add the topic to our internal array so we know who to notify about updates, however
     * in a few special cases, upon subscription we want to send a collection of data to "catch-up" the client with
     * our current state. In those cases, we'll emit a (Foo)SubscriptionEvent, and let the system send the bulk
     * data to the client.
     *
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
     * A client is attempting to publish content to a subscribed connections on a URI
     *
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

            case self::FINAL_JEOPARDY_TOPIC:
                if (!isset($event['content'])) {
                    //TODO logging
                    echo "Recieved invalid final jeopardy topic request - no content selection";
                    break;
                }

                if ($event['content'] == "category") {
                    $this->emitter->emit(new Question\FinalJeopardy\FinalJeopardyCategoryRequest());
                    break;
                }

                if ($event['content'] == "clue") {
                    $this->emitter->emit(new Question\FinalJeopardy\FinalJeopardyClueRequest());
                    break;
                }

                if ($event['content'] == "answer") {
                    $this->emitter->emit(new Question\FinalJeopardy\FinalJeopardyAnswerRequest());
                    break;
                }

                break;

            default:
                break;
        }

    }

    /**
     * Get the WAMP sessionId from a given connection.
     *
     * Since we use the WAMP protocol for all communications, we are guaranteed to have a sessionId, this method
     * will pull that ID out of a given connection object. This is used for when a client requests a "catch-up", we don't
     * want to spam all connected clients with old data, so we'll only send that data to the specific user that connected.
     *
     * @param ConnectionInterface $conn
     * @return string
     */
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

    /**
     * Our buzzer status has changed.
     *
     * This event will be fired either when a new client subscribes to buzzer_status updates, or when the buzzer has
     * been enabled/disabled.
     *
     * @param BuzzerStatus $status
     * @param array $blacklist
     * @param array $whitelist
     */
    public function onBuzzerStatusChange(BuzzerStatus $status, $blacklist = [ ], $whitelist = [ ])
    {
        if (!array_key_exists(self::BUZZER_STATUS_TOPIC, $this->subscribedTopics)) {
            return;
        }

        $this->subscribedTopics[self::BUZZER_STATUS_TOPIC]->broadcast($status->toJson(), $blacklist, $whitelist);
    }

    /**
     * When a user subscribes to the Contestant Score feed, they should get and update of all the current contestants
     *
     * @param Collection $contestants  A collection containing Contestant objects
     * @param string     $sessionId    The session ID of the user who subscribed.
     */
    public function onContestantScoreSubscription(Collection $contestants, $sessionId)
    {
        if (!array_key_exists(self::CONTESTANT_SCORE, $this->subscribedTopics)) {
            return;
        }

        $response = $contestants->map(function (Contestant $contestant) {
            return $contestant->toArray();
        })->toJson();

        $this->subscribedTopics[self::CONTESTANT_SCORE]->broadcast($response, [ ], [ $sessionId ]);
    }

    /**
     * We have a new subscriber to the question feed, which means we want to send them the data about all the currently
     * active questions.
     *
     * @param Collection $categories  A collection which contains Category objects.
     * @param string     $sessionId   The session ID of the user who subscribed.
     */
    public function onQuestionSubscribe(Collection $categories, $sessionId)
    {
        if (!array_key_exists(self::QUESTION_DISPLAY_TOPIC, $this->subscribedTopics)) {
            return;
        }

        $response = $categories->map(function (Category $category) {
            return $category->toArray();
        })->toJson();

        $this->subscribedTopics[self::QUESTION_DISPLAY_TOPIC]->broadcast($response, [ ], [ $sessionId ]);
    }

    /**
     * A question has been selected, and should be displayed to all clients.
     *
     * @param Question  $question
     * @param string    $category
     */
    public function onQuestionDisplay(Question $question, $category)
    {
        if (!array_key_exists(self::QUESTION_DISPLAY_TOPIC, $this->subscribedTopics)) {
            return;
        }

        $response = $question->toArray();
        $response['category'] = $category;
        $response = json_encode($response);

        $this->subscribedTopics[self::QUESTION_DISPLAY_TOPIC]->broadcast($response);
    }

    /**
     * An admin has filled in the value that a user wants to bet on the daily double. We will let the clients know
     * that they can display the actual question text now.
     *
     * @param Question $question
     * @param $category
     * @param $bet
     */
    public function onDailyDoubleBetRecieved(Question $question, $category, $bet)
    {
        if (!array_key_exists(self::DAILY_DOUBLE_BET_TOPIC, $this->subscribedTopics)) {
            return;
        }

        $response = $question->toArray();
        $response['category'] = $category;
        $response['bet'] = $bet;
        $response = json_encode($response);

        $this->subscribedTopics[self::DAILY_DOUBLE_BET_TOPIC]->broadcast($response);
    }

    /**
     * A user has answered a question. We need to let the clients know if they were correct, and the amount we should
     * increase or decrease their score by.
     *
     * @param Question\QuestionAnswer $questionAnswer
     */
    public function onQuestionAnswer(Question\QuestionAnswer $questionAnswer)
    {
        if (!array_key_exists(self::QUESTION_ANSWER_QUESTION, $this->subscribedTopics)) {
            return;
        }

        $response = $questionAnswer->toJson();

        $this->subscribedTopics[self::QUESTION_ANSWER_QUESTION]->broadcast($response);
    }

    /**
     * A question has been flagged for dismissal, which should occur when a user either gets a question correct
     * or when an admin explicitly dismisses a question.
     *
     * @param Question\QuestionDismissal $dismissal
     */
    public function onQuestionDismiss(Question\QuestionDismissal $dismissal)
    {
        if (!array_key_exists(self::QUESTION_DISMISS_TOPIC, $this->subscribedTopics)) {
            return;
        }

        $response = $dismissal->toJson();

        $this->subscribedTopics[self::QUESTION_DISMISS_TOPIC]->broadcast($response);
    }

    public function onFinalJeopardyRequest($requestType, Question\FinalJeopardyClue $finalJeopardyClue) {
        if (!array_key_exists(self::FINAL_JEOPARDY_TOPIC, $this->subscribedTopics)) {
            return;
        }

        $requestedData = ucfirst($requestType);
        $data = call_user_func([ $finalJeopardyClue, "get{$requestedData}"]);

        $response = json_encode([ $requestType => $data ]);

        $this->subscribedTopics[self::FINAL_JEOPARDY_TOPIC]->broadcast($response);
    }





    /**
     * When a new connection is opened it will be passed to this method
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     * @throws \Exception
     */
    function onOpen(ConnectionInterface $conn)
    {
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     * @throws \Exception
     */
    function onClose(ConnectionInterface $conn)
    {
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
    }

    /**
     * A request to unsubscribe from a topic has been made
     * @param \Ratchet\ConnectionInterface $conn
     * @param string|Topic $topic The topic to unsubscribe from
     */
    function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
    }

}
