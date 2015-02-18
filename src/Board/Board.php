<?php

namespace Depotwarehouse\Jeopardy\Board;

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
     */
    function __construct($contestants, $categories)
    {
        $this->contestants = new Collection($contestants);
        $this->categories = new Collection($categories);
    }



}
