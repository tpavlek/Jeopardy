<?php

namespace Depotwarehouse\Jeopardy\Board;

use Illuminate\Support\Collection;

class Category
{

    /** @var  string */
    protected $name;

    /** @var Collection */
    protected $questions;

    /**
     * @param $name
     * @param Question[] $questions
     */
    function __construct($name, array $questions)
    {
        $this->name = $name;
        $this->questions = new Collection($questions);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * Create a new instance of a category, given the proper data.
     *
     * @param $name
     * @param array $questions Should take the form of raw stdClass objects directly deserialized from json.
     * @return Category
     */
    public static function create($name, array $questions)
    {
        $curry = [];

        foreach ($questions as $question) {
            $q = new Question(
                new Clue($question->clue),
                new Answer($question->answer),
                $question->value
            );

            $curry[] = $q;
        }

        return new Category($name, $curry);
    }


}
