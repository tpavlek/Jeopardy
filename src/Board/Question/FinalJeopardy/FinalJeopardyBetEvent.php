<?php

namespace Depotwarehouse\Jeopardy\Board\Question\FinalJeopardy;

use League\Event\AbstractEvent;

class FinalJeopardyBetEvent extends AbstractEvent
{

    protected $contestant;
    protected $bet;

    public function __construct($contestant, $bet) {
        $this->contestant = $contestant;
        $this->bet = $bet;
    }


}
