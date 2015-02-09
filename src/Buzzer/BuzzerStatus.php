<?php

namespace Depotwarehouse\Jeopardy\Buzzer;

class BuzzerStatus
{

    protected $begin;
    protected $active = false;

    public function __construct()
    {
        $this->begin = microtime(true);
    }

    public function makeActive()
    {
        $this->active = true;
        $this->begin = microtime(true);
    }

    public function disable()
    {
        $this->active = false;
    }

    public function isActive()
    {
        return $this->active;
    }

}
