<?php

namespace Depotwarehouse\Jeopardy\Tests\Participant;


use Depotwarehouse\Jeopardy\Participant\ContestantFactory;

class ContestantFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function test_it_deserializes_stdClass()
    {
        $json = '{"name":"mock_contestant","score":100}';
        $factory = new ContestantFactory();
        $contestant = $factory->createFromObject(json_decode($json));

        // The contestant should have an uppercase first letter.
        $this->assertEquals("Mock_contestant", $contestant->getName());
        $this->assertEquals(100, $contestant->getScore());
    }

}
