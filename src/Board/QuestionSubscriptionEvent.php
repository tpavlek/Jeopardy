<?php

namespace Depotwarehouse\Jeopardy\Board;

use League\Event\AbstractEvent;

class QuestionSubscriptionEvent extends AbstractEvent
{

    /**
     * The sessionId of the user who has just subscribed to updates about Questions.
     * @var string
     */
    protected $sessionId;

    function __construct($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }


}
