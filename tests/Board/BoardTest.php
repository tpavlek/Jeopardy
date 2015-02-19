<?php

namespace Depotwarehouse\Jeopardy\Tests;


use Depotwarehouse\Jeopardy\Board\Board;
use Depotwarehouse\Jeopardy\Buzzer\BuzzerStatus;
use Depotwarehouse\Jeopardy\Buzzer\Resolver;
use Depotwarehouse\Jeopardy\Participant\Contestant;

class BoardTest extends \PHPUnit_Framework_TestCase
{

    public function test_it_can_update_scores()
    {
        $contestants = [
            new Contestant("Phil"),
            new Contestant("Bob")
        ];
        $categories = [];

        $board = new Board(
            $contestants,
            $categories,
            new Resolver(),
            new BuzzerStatus()
        );

        $board->addScore(new Contestant("Phil"), 200);
        $board->addScore(new Contestant("Bob"), 850);

        $phil = $board->getContestants()->offsetGet(0);
        $this->assertEquals(200, $phil->getScore());
        $this->assertEquals("Phil", $phil->getName());
    }

}
