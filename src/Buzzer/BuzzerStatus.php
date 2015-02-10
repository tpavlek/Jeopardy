<?php

namespace Depotwarehouse\Jeopardy\Buzzer;

use Illuminate\Contracts\Support\Jsonable;

class BuzzerStatus implements Jsonable
{

    protected $begin;
    protected $active;

    public function __construct($active = false)
    {
        $this->begin = microtime(true);
        $this->active = $active;
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

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode([
            'active' => $this->active
        ]);
    }
}
