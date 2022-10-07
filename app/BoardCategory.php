<?php

namespace App;

use App\Models\Category;
use App\Models\Clue;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class BoardCategory implements Arrayable
{

    public function __construct(public Category $category, public Collection $clues)
    {

    }

    public function toArray(): array
    {
        return [
            'name' => $this->category->name,
            'clues' => $this->clues->map(fn (Clue $clue) => [ 'status' => $clue->pivot->status, 'value' => $clue->value ])->values()->all(),
        ];
    }


}
