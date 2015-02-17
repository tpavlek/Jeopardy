<?php

namespace Depotwarehouse\Jeopardy\Board;

class Clue
{

    protected $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function getText()
    {
        return $this->text;
    }

}
