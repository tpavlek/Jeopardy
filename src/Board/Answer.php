<?php

namespace Depotwarehouse\Jeopardy\Board;

class Answer
{

    /** @var  string */
    protected $text;

    function __construct($text)
    {
        $this->text = $text;
    }

    public function __toString()
    {
        return $this->getText();
    }

    /**
     * @return string
     */
    public function getText()
    {
        return ($this->text !== null) ? $this->text : "";
    }

}
