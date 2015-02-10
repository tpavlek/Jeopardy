<?php

namespace Depotwarehouse\Jeopardy\Buzzer;

use League\Event\AbstractEvent;

class BuzzerStatusChangeEvent extends AbstractEvent
{

    protected $status;

    public function __construct(BuzzerStatus $status)
    {
        $this->status = $status;
    }

    public function getBuzzerStatus()
    {
        return $this->status;
    }

}
