<?php

namespace Depotwarehouse\Jeopardy\Buzzer;

use Illuminate\Support\Collection;

class Resolver
{
    /** @var  Collection */
    protected $buzzes;

    protected $firstBuzz;

    public function __construct(array $items = []) {
        $this->buzzes = new Collection($items);
    }

    public function isEmpty()
    {
        return $this->buzzes->isEmpty();
    }

    /**
     * Add a buzz to the resolver.
     *
     * We don't care about adding duplicate buzzes for the same contestant, because their lowest one will always be
     * the one that is selected.
     *
     * @param BuzzReceivedEvent $event
     */
    public function addBuzz(BuzzReceivedEvent $event)
    {
        $this->buzzes->push($event);
    }

    /**
     * Determines a winner from the list of buzzes accumulated.
     *
     * Note: This method is destructive, and will clear the list of buzzes on completion.
     * @return BuzzerResolution
     */
    public function resolve()
    {
        if ($this->isEmpty()) {
            return BuzzerResolution::createFailure();
        }

        /** @var BuzzReceivedEvent $winner */
        $winner = $this->buzzes->reduce(
            function(BuzzReceivedEvent $carry, BuzzReceivedEvent $event) {

                if ($event->getDifference() < $carry->getDifference()) {
                    return $event;
                }
                return $carry;
            },
            $this->buzzes->first()
        );

        $resolution = BuzzerResolution::createSuccess($winner->getContestant(), $winner->getDifference());
        $this->buzzes = new Collection();

        return $resolution;

    }

}
