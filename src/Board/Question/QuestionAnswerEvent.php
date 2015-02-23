<?php

namespace Depotwarehouse\Jeopardy\Board\Question;

use League\Event\AbstractEvent;

class QuestionAnswerEvent extends AbstractEvent
{

    protected $questionAnswer;

    public function __construct(QuestionAnswer $questionAnswer)
    {
        $this->questionAnswer = $questionAnswer;
    }

    public function getQuestionAnswer()
    {
        return $this->questionAnswer;
    }


}
