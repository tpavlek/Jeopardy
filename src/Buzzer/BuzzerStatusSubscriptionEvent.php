<?php

namespace Depotwarehouse\Jeopardy\Buzzer;

use League\Event\AbstractEvent;

class BuzzerStatusSubscriptionEvent extends AbstractEvent
{

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
