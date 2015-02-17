<?php

namespace Depotwarehouse\Jeopardy\Tests;


use Depotwarehouse\Jeopardy\Board\Category;
use Depotwarehouse\Jeopardy\Board\Question;
use Illuminate\Support\Collection;

class CategoryTest extends \PHPUnit_Framework_TestCase
{

    public function test_it_creates_from_static_method()
    {
        $name = "mock_category";
        $questions = [
            (object)[ 'clue' => "mock_clue_1", 'answer' => "mock_answer_1", 'value' => 200 ],
            (object)[ 'clue' => "mock_clue_2", 'answer' => "mock_answer_2", 'value' => 400 ],
        ];

        $category = Category::create($name, $questions);

        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals($name, $category->getName());

        $this->assertInstanceOf(Collection::class, $category->getQuestions());
        $this->assertEquals(2, $category->getQuestions()->count());
        $this->assertInstanceOf(Question::class, $category->getQuestions()->first());
    }

}
