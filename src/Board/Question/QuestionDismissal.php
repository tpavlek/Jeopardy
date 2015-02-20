<?php

namespace Depotwarehouse\Jeopardy\Board\Question;

use Depotwarehouse\Jeopardy\Board\Question;
use Depotwarehouse\Jeopardy\Participant\Contestant;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class QuestionDismissal implements Arrayable, Jsonable
{

    /** @var  Contestant */
    protected $winner;

    /**
     * If this was a daily double, then there is a bet included.
     *
     * @var int
     */
    protected $bet = null;

    protected $category;
    protected $value;

    public function __construct($category, $value)
    {
        $this->category = $category;
        $this->value = $value;
    }

    /**
     * Get the actual value of this clue for the winner.
     *
     * If this was a Daily Double, it will be whatever they bet. Otherwise it will be the regular value of the clue.
     *
     * @return int
     */
    public function getRealValue()
    {
        if ($this->bet !== null) {
            return $this->bet;
        }

        return $this->value;
    }

    public function setBet($bet)
    {
        $this->bet = $bet;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return Contestant
     */
    public function getWinner()
    {
        return $this->winner;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }


    /**
     * @param Contestant $contestant
     * @return $this
     */
    public function setWinner(Contestant $contestant)
    {
        $this->winner = $contestant;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasWinner()
    {
        return $this->winner !== null;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'has_winner' => $this->hasWinner(),
            'category' => $this->getCategory(),
            'value' => $this->getValue(),
            'winner' => ($this->hasWinner()) ? $this->getWinner()->getName() : null,
            'bet' => $this->getRealValue()
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
