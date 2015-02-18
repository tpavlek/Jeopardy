<?php

namespace Depotwarehouse\Jeopardy\Tests;

use Depotwarehouse\Jeopardy\Board\Board;
use Depotwarehouse\Jeopardy\Board\BoardFactory;
use PHPUnit_Framework_TestCase;

class BoardFactoryTest extends PHPUnit_Framework_TestCase
{

    public function test_it_deserializes_from_json()
    {
        $board = BoardFactory::fromJson(file_get_contents("tests/questions.json"));

        $this->assertEquals(3, $board->getContestants()->count());
        $this->assertEquals(2, $board->getCategories()->count());
    }

}
