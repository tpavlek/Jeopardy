<?php

namespace Depotwarehouse\Jeopardy;

use Depotwarehouse\Jeopardy\Buzzer\BuzzerResolution;
use Depotwarehouse\Jeopardy\Buzzer\BuzzerResolutionEvent;
use Depotwarehouse\Jeopardy\Buzzer\BuzzReceivedEvent;
use Depotwarehouse\Jeopardy\Buzzer\Resolver;
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

    /**
     * The time, in seconds, in which we want to wait after receiving the first buzz before resolving a winner.
     * @var float
     */
    protected $buzzer_resolve_timeout = 5;

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

        $resolver = new Resolver();

        $emitter->addListener(BuzzerResolutionEvent::class, function(BuzzerResolutionEvent $event) use ($wamp) {
            $wamp->onBuzzerResolution($event->getResolution());
        });

        $emitter->addListener(BuzzReceivedEvent::class, function(BuzzReceivedEvent $event) use ($resolver, $emitter) {

            if ($resolver->isEmpty()) {
                // If this is the first buzz, then we want to resolve it after the timeout.
                $this->eventLoop->addTimer($this->buzzer_resolve_timeout, function() use ($resolver, $emitter) {
                    $resolution = $resolver->resolve();
                    $emitter->emit(new BuzzerResolutionEvent($resolution));
                });
            }

            $resolver->addBuzz($event);

        });

        $this->eventLoop->run();
    }

}
