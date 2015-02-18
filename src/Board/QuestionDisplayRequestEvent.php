<?php

namespace Depotwarehouse\Jeopardy\Board;

use League\Event\AbstractEvent;

class QuestionDisplayRequestEvent extends AbstractEvent
{

    protected $categoryName;
    protected $value;

    function __construct($categoryName, $value)
    {
        $this->categoryName = $categoryName;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

}
