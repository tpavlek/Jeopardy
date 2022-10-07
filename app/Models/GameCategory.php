<?php

namespace App\Models;

use App\GameRound;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GameCategory extends Pivot
{
    public function matchesRound(GameRound $round): bool
    {
        return $this->round === $round->value;
    }
}
