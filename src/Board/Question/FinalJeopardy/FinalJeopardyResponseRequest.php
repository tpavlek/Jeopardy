<?php

namespace Depotwarehouse\Jeopardy\Board\Question\FinalJeopardy;

use League\Event\AbstractEvent;

class FinalJeopardyResponseRequest extends AbstractEvent
{

    protected $contestant;

    public function __construct($contestant)
    {
        $this->contestant = $contestant;
    }

    /**
     * @return string
     */
    public function getContestant()
    {
        return $this->contestant;
    }



}
