<?php

namespace Depotwarehouse\Jeopardy\Board\Question;

use Depotwarehouse\Jeopardy\Board\Question;
use Depotwarehouse\Jeopardy\Participant\Contestant;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class QuestionDismissal implements Arrayable, Jsonable
{

    protected $category;
    protected $value;

    public function __construct($category, $value)
    {
        $this->category = $category;
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
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
            'value' => $this->getValue(),
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
