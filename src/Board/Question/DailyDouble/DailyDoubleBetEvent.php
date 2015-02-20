<?php

namespace Depotwarehouse\Jeopardy\Board\Question\DailyDouble;

use League\Event\AbstractEvent;

class DailyDoubleBetEvent extends AbstractEvent
{

    protected $value;
    protected $category;
    protected $bet;

    function __construct($value, $category, $bet)
    {
        $this->value = $value;
        $this->category = $category;
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
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return int
     */
    public function getBet()
    {
        return $this->bet;
    }




}
