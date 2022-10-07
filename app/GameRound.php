<?php

namespace App;

enum GameRound: string
{
    case Single = "single";
    case Double = "double";
    case Final = "final";

    public function toRoundNumber(): int
    {
        return match($this) {
            GameRound::Single => 1,
            GameRound::Double => 2,
            default => throw new \InvalidArgumentException("Can't do")
        };
    }
}
