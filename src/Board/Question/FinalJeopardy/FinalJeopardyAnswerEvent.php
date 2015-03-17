<?php

namespace Depotwarehouse\Jeopardy\Board\Question\FinalJeopardy;

use League\Event\AbstractEvent;

class FinalJeopardyAnswerEvent extends AbstractEvent
{

    protected $contestant;
    protected $answer;

    public function __construct($contestant, $answer)
    {
        $this->contestant = $contestant;
        $this->answer = $answer;
    }

}
