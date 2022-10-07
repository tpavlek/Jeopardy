<?php

namespace App;

use App\Models\Game;
use Illuminate\Support\Facades\Redis;

class Buzzer
{

    public function __construct(public Game $game)
    {

    }

    public function open(): self
    {
        Redis::set($this->getKey(), BuzzerStatus::Open->value);

        return $this;
    }

    public function status(): BuzzerStatus
    {
        return BuzzerStatus::from(Redis::get($this->getKey()));
    }

    private function getKey(): string
    {
        return "{$this->game->slug}_buzzer";
    }

}
