<?php

namespace Depotwarehouse\Jeopardy\Board\Question\FinalJeopardy;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class FinalJeopardyQuestionResponse implements Arrayable, Jsonable
{

    protected $bet;
    protected $answer;
    protected $contestant;

    public function __construct($contestant, $bet, $answer)
    {
        $this->contestant = $contestant;
        $this->bet = $bet;
        $this->answer = $answer;
    }

    /**
     * @return int
     */
    public function getBet()
    {
        return $this->bet;
    }

    /**
     * @return string
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @return string
     */
    public function getContestant()
    {
        return $this->contestant;
    }


    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'contestant' => $this->getContestant(),
            'bet' => $this->getBet(),
            'answer' => $this->getAnswer()
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
