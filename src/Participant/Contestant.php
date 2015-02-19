<?php

namespace Depotwarehouse\Jeopardy\Participant;

class Contestant
{

    /** @var  string */
    protected $name;

    protected $score = 0;

    function __construct($name)
    {
        $this->name = $name;
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


}
