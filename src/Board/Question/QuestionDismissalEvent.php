<?php

namespace Depotwarehouse\Jeopardy\Board\Question;

use League\Event\AbstractEvent;

class QuestionDismissalEvent extends AbstractEvent
{

    /** @var  QuestionDismissal */
    protected $dismissal;

    function __construct($dismissal)
    {
        $this->dismissal = $dismissal;
    }

    /**
     * @return QuestionDismissal
     */
    public function getDismissal()
    {
        return $this->dismissal;
    }






}
