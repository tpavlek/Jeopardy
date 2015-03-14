<?php

namespace Depotwarehouse\Jeopardy\Participant;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class Contestant implements Arrayable, Jsonable
{

    /** @var  string */
    protected $name;

    protected $score;

    public function __construct($name, $score = 0)
    {
        $this->name = $name;
        $this->score = $score;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getScore()
    {
        return $this->score;
    }

    public function addScore($value)
    {
        $this->score += $value;
    }


    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'score' => $this->getScore()
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
