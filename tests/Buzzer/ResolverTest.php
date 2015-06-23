<?php

namespace Depotwarehouse\Jeopardy\Tests\Buzzer;

use Depotwarehouse\Jeopardy\Buzzer\BuzzerResolution;
use Depotwarehouse\Jeopardy\Buzzer\BuzzReceivedEvent;
use Depotwarehouse\Jeopardy\Buzzer\Resolver;
use Depotwarehouse\Jeopardy\Participant\Contestant;
use PHPUnit_Framework_TestCase;

class ResolverTest extends PHPUnit_Framework_TestCase
{

    public function test_it_sorts_events()
    {
        $events = [
            new BuzzReceivedEvent(
                new Contestant("Fred"),
                20
            ),
            new BuzzReceivedEvent(
                new Contestant("Joe"),
                60
            ),
            new BuzzReceivedEvent(
                new Contestant("Murphy"),
                10
            )
        ];

        $resolver = new Resolver($events);
        $resolution = $resolver->resolve();

        $this->assertInstanceOf(BuzzerResolution::class, $resolution);
        $this->assertTrue($resolution->hasWinner());
        $this->assertEquals("Murphy", $resolution->getContestant()->getName());
        $this->assertEquals(10, $resolution->getTime());
    }
}
