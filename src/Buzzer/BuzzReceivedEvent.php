<?php

namespace Depotwarehouse\Jeopardy\Buzzer;

use Depotwarehouse\Jeopardy\Participant\Contestant;
use League\Event\AbstractEvent;

class BuzzReceivedEvent extends AbstractEvent
{

    /** @var Contestant */
    protected $contestant;
    /**
     * The client-provided value that represents the difference in time from when the buzzer went active until they
     * submitted.
     * @var int
     */
    protected $difference;

    public function __construct(Contestant $contestant, $difference)
    {
        $this->contestant = $contestant;
        $this->difference = $difference;
    }


    /**
     * @return Contestant
     */
    public function getContestant()
    {
        return $this->contestant;
    }

    /**
     * @return int
     */
    public function getDifference()
    {
        return $this->difference;
    }
}
