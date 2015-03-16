<?php

namespace Depotwarehouse\Jeopardy\Board\Question;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class FinalJeopardyClue implements Arrayable, Jsonable
{

    protected $category;
    protected $clue;
    protected $answer;

    public function __construct($category, $clue, $answer)
    {
        $this->category = $category;
        $this->clue = $clue;
        $this->answer = $answer;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getClue()
    {
        return $this->clue;
    }

    /**
     * @return string
     */
    public function getAnswer()
    {
        return $this->answer;
    }


    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'category' => $this->getCategory(),
            'clue' => $this->getClue(),
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
        // TODO: Implement toJson() method.
    }
}
