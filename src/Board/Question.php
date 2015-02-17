<?php

namespace Depotwarehouse\Jeopardy\Board;

class Question
{

    protected $clue;
    protected $answer;
    /** @var  int */
    protected $value;

    public function __construct(Clue $clue, Answer $answer, $value)
    {
        $this->clue = $clue;
        $this->answer = $answer;
        $this->value = $value;
    }

    /**
     * @return Clue
     */
    public function getClue()
    {
        return $this->clue;
    }

    /**
     * @return Answer
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    public static function createFromJson($json)
    {
        $values = json_decode($json);

        return new Question(
            new Clue($json->clue),
            new Answer($json->answer),
            $json->value
        );
    }


}
