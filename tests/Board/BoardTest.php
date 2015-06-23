<?php

namespace Depotwarehouse\Jeopardy\Tests;


use Depotwarehouse\Jeopardy\Board\Board;
use Depotwarehouse\Jeopardy\Board\Question\FinalJeopardy;
use Depotwarehouse\Jeopardy\Board\Question\FinalJeopardyClue;
use Depotwarehouse\Jeopardy\Buzzer\BuzzerStatus;
use Depotwarehouse\Jeopardy\Buzzer\BuzzReceivedEvent;
use Depotwarehouse\Jeopardy\Buzzer\Resolver;
use Depotwarehouse\Jeopardy\Participant\Contestant;
use Illuminate\Support\Collection;

class BoardTest extends \PHPUnit_Framework_TestCase
{

    public function test_it_can_update_scores()
    {
        $contestants = new Collection([
            new Contestant("Phil"),
            new Contestant("Bob")
        ]);
        $categories = [];

        $board = new Board(
            $contestants,
            $categories,
            new Resolver(),
            new BuzzerStatus(),
            new FinalJeopardy\State(
                new FinalJeopardyClue("mock_category", "mock_clue", "mock_answer"),
                $contestants->map(function(Contestant $contestant) { return $contestant->getName(); })->toArray()
            )
        );

        $board->addScore(new Contestant("Phil"), 200);
        $board->addScore(new Contestant("Bob"), 850);

        $phil = $board->getContestants()->offsetGet(0);
        $this->assertEquals(200, $phil->getScore());
        $this->assertEquals("Phil", $phil->getName());
    }

    public function test_it_can_resolve_buzzes()
    {
        $contestants = new Collection([
            new Contestant("Phil"),
            new Contestant("Bob")
        ]);
        $categories = [];

        $board = new Board(
            $contestants,
            $categories,
            new Resolver(),
            new BuzzerStatus(),
            new FinalJeopardy\State(
                new FinalJeopardyClue("mock_category", "mock_clue", "mock_answer"),
                $contestants->map(function(Contestant $contestant) { return $contestant->getName(); })->toArray()
            )
        );

        $board->getResolver()->addBuzz(new BuzzReceivedEvent(new Contestant("Phil"), 90));
        $board->getResolver()->addBuzz(new BuzzReceivedEvent(new Contestant("Phil"), 50));
        $board->getResolver()->addBuzz(new BuzzReceivedEvent(new Contestant("Bob"), 70));

        $resolution = $board->resolveBuzzes();

        $this->assertEquals(50, $resolution->getTime());
        $this->assertEquals("Phil", $resolution->getContestant()->getName());
    }

}
