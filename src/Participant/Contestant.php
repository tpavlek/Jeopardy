<?php

namespace Depotwarehouse\Jeopardy\Participant;

class Contestant
{

    /** @var  string */
    protected $name;

    function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }




}
