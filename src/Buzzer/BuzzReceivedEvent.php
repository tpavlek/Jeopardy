<?php

namespace Depotwarehouse\Jeopardy\Buzzer;

use Depotwarehouse\Jeopardy\Participant\Contestant;
use League\Event\AbstractEvent;

class BuzzReceivedEvent extends AbstractEvent
{

    /** @var Contestant  */
    protected $contestant;

    public function __construct(Contestant $contestant) {
        $this->contestant = $contestant;
    }

    /**
     * Get the event name.
     *
     * @return string
     */
    public function getName()
    {
        return "BuzzReceivedEvent";
    }


    /**
     * @return Contestant
     */
    public function getContestant() {
        return $this->contestant;
    }
}
