<?php

namespace Depotwarehouse\Jeopardy\Participant;

use League\Event\AbstractEvent;

class ContestantScoreChangeEvent extends AbstractEvent
{

    protected $contestant;
    protected $scoreChange;

    public function __construct($contestant, $scoreChange)
    {
        $this->contestant = $contestant;
        $this->scoreChange = $scoreChange;
    }

    /**
     * @return string
     */
    public function getContestant()
    {
        return $this->contestant;
    }

    /**
     * @return int
     */
    public function getScoreChange()
    {
        return $this->scoreChange;
    }




}
