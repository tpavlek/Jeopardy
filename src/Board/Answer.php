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

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

}
