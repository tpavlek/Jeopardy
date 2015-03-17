<?php

namespace Depotwarehouse\Jeopardy\Board\Question\FinalJeopardy;

use Depotwarehouse\Jeopardy\Board\Question\FinalJeopardyClue;
use Illuminate\Support\Collection;

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
        $this->contestants = [ ];
        foreach ($contestants as $contestant) {
            $this->contestants[$contestant] = [ ];
        }
    }

    public function setBet($contestant, $bet)
    {
        if (!array_key_exists($contestant, $this->contestants)) {
            // We should not be in this state, log it.
            $this->contestants[$contestant] = [ ];
        }

        $this->contestants[$contestant]['bet'] = $bet;
    }

    public function setAnswer($contestant, $answer)
    {
        if (!array_key_exists($contestant, $this->contestants)) {
            // TODO Log
            $this->contestants[$contestant] = [ ];
        }

        $this->contestants[$contestant]['answer'] = $answer;
    }

    /**
     * @return Collection
     */
    protected function findMissingBets()
    {
        $hasBet = function ($contestant) {
            return !isset($contestant['bet']);
        };

        return (new Collection($this->contestants))->filter($hasBet);
    }

    protected function findMissingAnswers()
    {
        $hasAnswer = function($contestant) {
            return !isset($contestant['answer']);
        };

        return (new Collection($this->contestants))->filter($hasAnswer);
    }

    public function hasAllBets()
    {
        return $this->findMissingBets()->count() === 0;

    }

    public function hasAllAnswers()
    {
        return $this->findMissingAnswers()->count() === 0;
    }

    /**
     * @return array
     */
    public function getMissingBets()
    {
        return $this->findMissingBets()->keys()->toArray();
    }

    public function getMissingAnswers()
    {
        return $this->findMissingAnswers()->keys()->toArray();
    }

    public function hasAnswer($contestant)
    {
        if (!array_key_exists($contestant, $this->contestants)) {
            return false;
        }

        return isset($this->contestants[$contestant]['answer']);
    }

    public function getResponse($contestant)
    {
        $arr = $this->contestants[$contestant];
        $response = new FinalJeopardyQuestionResponse($contestant, $arr['bet'], $arr['answer']);
        return $response;
    }

    public function getClue()
    {
        return $this->clue;
    }

}
