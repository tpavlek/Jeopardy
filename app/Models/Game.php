<?php

namespace App\Models;

use App\BoardCategory;
use App\GameRound;
use App\GameStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Game extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'status' => GameStatus::class,
        'round_status' => GameRound::class,
    ];

    public function clues(): BelongsToMany
    {
        return $this->belongsToMany(Clue::class)
            ->withPivot([ 'status' ])
            ->using(GameClue::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)
            ->withPivot([ 'round', 'file' ])
            ->using(GameCategory::class);
    }

    public function markShowingClue(): self
    {
        $this->status = GameStatus::ShowingClue->value;
        $this->save();

        return $this;
    }

    public function getBoard(): Collection
    {
        $categories = $this->categories
            ->filter(fn (Category $category) => $category->pivot->matchesRound($this->round_status))
            ->sortBy(fn (Category $category) => $category->pivot->file)
            ->map(function (Category $category) {
                $clues = $this->clues->filter(fn (Clue $clue) => $clue->category_id === $category->id);
                return new BoardCategory($category, $clues);
            });

        return $categories;
    }

    public function getGameClue(Clue $clue): GameClue
    {
        return $this->clues->where('id', $clue->id)->firstOrFail()->pivot;
    }

    public function clueAt(Category $category, int $value): Clue
    {
        return $this->clues
            ->where('value', $value)
            ->where('category_id', $category->id)
            ->firstOrFail();
    }
}
