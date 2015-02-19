<?php

namespace Depotwarehouse\Jeopardy\Participant;

use League\Event\AbstractEvent;

class ContestantScoreSubscriptionEvent extends AbstractEvent
{

    /** @var  string */
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
