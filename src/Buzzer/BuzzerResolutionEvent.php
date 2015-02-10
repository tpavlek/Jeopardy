<?php

namespace Depotwarehouse\Jeopardy\Buzzer;

use League\Event\AbstractEvent;

class BuzzerResolutionEvent extends AbstractEvent
{

    protected $resolution;

    public function __construct(BuzzerResolution $resolution)
    {
        $this->resolution = $resolution;
    }

    /**
     * Get the resolution.
     *
     * @return BuzzerResolution
     */
    public function getResolution()
    {
        return $this->resolution;
    }

}
