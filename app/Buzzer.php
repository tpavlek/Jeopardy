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

    public function control(BuzzerStatus $status): self
    {
        Redis::set($this->getKey(), $status->value);

        return $this;
    }

    public function status(): BuzzerStatus
    {
        return BuzzerStatus::tryFrom(Redis::get($this->getKey())) ?? BuzzerStatus::Closed;
    }

    private function getKey(): string
    {
        return "{$this->game->slug}_buzzer";
    }

}
