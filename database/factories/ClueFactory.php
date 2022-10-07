<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Clue>
 */
class ClueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'text' => $this->faker->text(50),
            'uuid' => $this->faker->uuid,
        ];
    }

    public function valueSequence(int $round = 1)
    {
        $this->sequence(fn ($sequence) => [ 'value' => ($sequence->index + 1) * ($round * 200) ]);
        return $this;
    }
}
