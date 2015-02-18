<?php

namespace Depotwarehouse\Jeopardy\Board;

use Illuminate\Contracts\Support\Arrayable;

class Question implements Arrayable
{

    protected $clue;
    protected $answer;
    /** @var  int */
    protected $value;

    /**
     * Have we already used this question in this round?
     * @var bool
     */
    protected $used = false;

    public function __construct(Clue $clue, Answer $answer, $value)
    {
        $this->clue = $clue;
        $this->answer = $answer;
        $this->value = $value;
    }

    /**
     * @return bool
     */
    public function isUsed()
    {
        return $this->used;
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


    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'clue' => (string)$this->getClue(),
            'answer' => (string)$this->getAnswer(),
            'value' => (int)$this->getValue(),
            'used' => (bool)$this->isUsed()
        ];
    }
}
