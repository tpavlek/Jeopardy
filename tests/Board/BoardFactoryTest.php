<?php

namespace Depotwarehouse\Jeopardy\Tests;

use Depotwarehouse\Jeopardy\Board\Board;
use Depotwarehouse\Jeopardy\Board\BoardFactory;
use Depotwarehouse\Jeopardy\Board\Question\FinalJeopardyClue;
use PHPUnit_Framework_TestCase;

class BoardFactoryTest extends PHPUnit_Framework_TestCase
{

    public function test_it_deserializes_from_json()
    {
        $factory = new BoardFactory('questions', 'tests/');
        $board = $factory->initialize();

        $this->assertEquals(3, $board->getContestants()->count());
        $this->assertEquals(2, $board->getCategories()->count());
        $this->assertInstanceOf(FinalJeopardyClue::class, $board->getFinalJeopardyClue());
        $this->assertEquals("mock_final_clue", $board->getFinalJeopardyClue()->getClue());
    }

}
