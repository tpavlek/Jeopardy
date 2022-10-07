<?php

namespace App\Models;

use App\ClueStatus;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GameClue extends Pivot
{

    protected $casts = [
        'status' => ClueStatus::class
    ];

    public $incrementing = true;

    public function markRevealed(): self
    {
        $this->status = ClueStatus::Revealed->value;
        $this->save();

        return $this;
    }

    public function markDismissed(): self
    {
        $this->status = ClueStatus::Unavailable->value;
        $this->save();

        return $this;
    }

    public function isAvailable(): bool
    {
        return $this->status === ClueStatus::Available->value;
    }
}
