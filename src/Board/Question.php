<?php

namespace Depotwarehouse\Jeopardy\Board;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class Question implements Arrayable, Jsonable
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

    protected $isDailyDouble = false;

    public function __construct(Clue $clue, Answer $answer, $value, $isDailyDouble = false)
    {
        $this->clue = $clue;
        $this->answer = $answer;
        $this->value = $value;
        $this->isDailyDouble = $isDailyDouble;
    }

    /**
     * @return bool
     */
    public function isUsed()
    {
        return $this->used;
    }

    /**
     * Sets a question as used for this game.
     *
     * @param bool $used
     * @return $this
     */
    public function setUsed($used = true)
    {
        $this->used = $used;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDailyDouble()
    {
        return $this->isDailyDouble;
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
            'used' => (bool)$this->isUsed(),
            'daily_double' => $this->isDailyDouble()
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray());
    }
}
