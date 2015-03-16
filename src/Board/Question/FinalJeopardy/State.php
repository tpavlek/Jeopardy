<?php

namespace Depotwarehouse\Jeopardy\Board\Question\FinalJeopardy;

use Depotwarehouse\Jeopardy\Board\Question\FinalJeopardyClue;

class State
{

    protected $clue;

    /**
     * A key-value multidimensional array.
     *
     * Takes the form
     *
     * ```
     * [
     *     'contestant_name' => [
     *         'bet' => 100,
     *         'answer' => "some Answer"
     *     ]
     * ]
     * ```
     * @var array
     */
    protected $contestants;

    public function __construct(FinalJeopardyClue $clue, array $contestants)
    {
        $this->clue = $clue;
        $this->contestants = $contestants;
    }

    public function setBet($contestant, $bet)
    {
        if (!array_key_exists($contestant, $this->contestants)) {
            // We should not be in this state, log it.
            $this->contestants[$contestant] = [];
        }

        $this->contestants[$contestant]['bet'] = $bet;
    }

    public function setAnswer($contestant, $answer) {
        if (!array_key_exists($contestant, $this->contestants)) {
            // TODO Log
            $this->contestants[$contestant] = [];
        }

        $this->contestants[$contestant]['answer'] = $answer;
    }


    public function getClue()
    {
        return $this->clue;
    }

}
