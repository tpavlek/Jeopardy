<?php

namespace Depotwarehouse\Jeopardy\Board;

use Depotwarehouse\Jeopardy\Buzzer\BuzzerStatus;
use Depotwarehouse\Jeopardy\Buzzer\Resolver;
use Depotwarehouse\Jeopardy\Participant\Contestant;
use Illuminate\Support\Collection;

class Board
{

    /**
     * A collection of contestants.
     * @var Collection
     */
    protected $contestants;
    /**
     * A collection of Categories.
     * @var Collection
     */
    protected $categories;

    /**
     * The buzzer resolver which resolves who won a particular buzz.
     * @var Resolver
     */
    protected $resolver;

    /**
     * @return BuzzerStatus
     */
    public function getBuzzerStatus()
    {
        return $this->buzzerStatus;
    }

    /**
     * @param BuzzerStatus $buzzerStatus
     * @return $this
     */
    public function setBuzzerStatus(BuzzerStatus $buzzerStatus)
    {
        $this->buzzerStatus = $buzzerStatus;
        return $this;
    }

    /**
     * The current status of the buzzer.
     * @var BuzzerStatus
     */
    protected $buzzerStatus;

    /**
     * @return mixed
     */
    public function getResolver()
    {
        return $this->resolver;
    }

    /**
     * Resolves the current buzzer competition and returns the resolution.
     * As a side-effect, this will also disable the buzzer.
     *
     * @return \Depotwarehouse\Jeopardy\Buzzer\BuzzerResolution
     */
    public function resolveBuzzes()
    {
        $resolution = $this->resolver->resolve();
        $this->buzzerStatus->disable();
        return $resolution;
    }

    /**
     * @return Collection
     */
    public function getContestants()
    {
        return $this->contestants;
    }

    /**
     * @return Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }


    /**
     * @param Contestant[]|Collection $contestants
     * @param Category[]|Collection $categories
     * @param Resolver $resolver
     * @param BuzzerStatus $buzzerStatus
     */
    function __construct($contestants, $categories, Resolver $resolver, BuzzerStatus $buzzerStatus)
    {
        $this->contestants = new Collection($contestants);
        $this->categories = new Collection($categories);
        $this->resolver = $resolver;
        $this->buzzerStatus = $buzzerStatus;
    }


}
