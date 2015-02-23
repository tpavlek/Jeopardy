<?php

namespace Depotwarehouse\Jeopardy\Board\Question;

use Depotwarehouse\Jeopardy\Participant\Contestant;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class QuestionAnswer implements Arrayable, Jsonable
{

    /**
     * If this was a daily double, then there is a bet included.
     *
     * @var int
     */
    protected $bet = null;

    protected $category;

    /** @var Contestant */
    protected $contestant;

    /** @var  int */
    protected $value;

    /** @var bool */
    protected $correct;

    public function __construct($category, $value, Contestant $contestant, $correct = false)
    {
        $this->category = $category;
        $this->value = $value;
        $this->contestant = $contestant;
        $this->correct = $correct;
    }

    public function setBet($bet)
    {
        $this->bet = $bet;
        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Gets the actual value of the response, which will be negative if the response was incorrect.
     *
     * if this was a daily double, the value will be the amount that the user bet.
     * @return int
     */
    public function getRealValue()
    {
        $value = ($this->bet !== null) ? $this->bet : $this->value;
        return $value * (($this->isCorrect()) ? 1 : -1);
    }

    /**
     * @return Contestant
     */
    public function getContestant()
    {
        return $this->contestant;
    }

    /**
     * @return boolean
     */
    public function isCorrect()
    {
        return $this->correct;
    }


    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'contestant' => $this->getContestant()->getName(),
            'value' => $this->getRealValue(),
            'correct' => $this->isCorrect()
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
