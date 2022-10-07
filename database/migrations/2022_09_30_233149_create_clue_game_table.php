<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clue_game', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(\App\Models\Game::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Clue::class)->constrained()->cascadeOnDelete();

            $table->string('status')->default(\App\ClueStatus::Available->value);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clue_game');
    }
};
