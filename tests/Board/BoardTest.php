<?php

namespace Depotwarehouse\Jeopardy\Tests;

use Depotwarehouse\Jeopardy\Board\Board;
use PHPUnit_Framework_TestCase;

class BoardTest extends PHPUnit_Framework_TestCase
{

    public function test_it_deserializes_from_json()
    {
        $board = Board::fromJson(file_get_contents("tests/questions.json"));
    }

}
