<?php

namespace Depotwarehouse\Jeopardy\Board;

use Illuminate\Contracts\Support\Arrayable;

class Clue implements Arrayable
{

    protected $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function __toString()
    {
        return $this->getText();
    }

    public function getText()
    {
        return $this->text;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'text' => $this->getText()
        ];
    }
}
