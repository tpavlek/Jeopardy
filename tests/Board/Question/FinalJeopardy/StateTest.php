<?php

namespace Depotwarehouse\Jeopardy\Tests\Board\Question\FinalJeopardy;


use Depotwarehouse\Jeopardy\Board\Question\FinalJeopardy\State;
use Depotwarehouse\Jeopardy\Board\Question\FinalJeopardyClue;

class StateTest extends \PHPUnit_Framework_TestCase {

    public function setUp()
    {
        parent::setUp();
    }
    
    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_it_returns_array_of_players_who_have_not_buzzed()
    {
        $state = new State(new FinalJeopardyClue("mock_category", "mock_clue", "mock_answer"), [ "one", "two", "three" ]);
        $this->assertEquals([ "one", "two", "three" ], $state->getMissingBets());
    }

}
