<?php

namespace Depotwarehouse\Jeopardy;

use Depotwarehouse\Jeopardy\Buzzer\BuzzerResolution;
use Depotwarehouse\Jeopardy\Buzzer\BuzzReceivedEvent;
use Depotwarehouse\Jeopardy\Participant\Contestant;
use League\Event\Emitter;
use League\Event\Event;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\Wamp\WampServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

class Server
{

    /** @var  LoopInterface */
    protected $eventLoop;

    public function __construct(LoopInterface $loopInterface)
    {
        $this->eventLoop = $loopInterface;
    }

    public function run()
    {
        $emitter = new Emitter();
        $wamp = new WampConnector($emitter);

        $webSocket = new \React\Socket\Server($this->eventLoop);
        $webSocket->listen(9001, '0.0.0.0');

        $webServer = new IoServer(
            new HttpServer(
                new WsServer(
                    new WampServer(
                        $wamp
                    )
                )
            ),
            $webSocket
        );

        $emitter->addListener("BuzzReceivedEvent", function(BuzzReceivedEvent $event) {
            echo "Event recieved";
        });

        $i = 0;

        $this->eventLoop->addPeriodicTimer(5, function() use ($wamp, $emitter, &$i) {

            /*$resolution = BuzzerResolution::createSuccess(
                new Contestant("Fred"), microtime(true)
            );
            $wamp->onBuzzerResolution($resolution);*/




        });

        $this->eventLoop->run();
    }

}
