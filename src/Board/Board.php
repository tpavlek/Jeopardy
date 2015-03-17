<?php

namespace Depotwarehouse\Jeopardy\Board;

use Depotwarehouse\Jeopardy\Board\Question\FinalJeopardy;
use Depotwarehouse\Jeopardy\Board\Question\FinalJeopardyClue;
use Depotwarehouse\Jeopardy\Buzzer\BuzzerStatus;
use Depotwarehouse\Jeopardy\Buzzer\Resolver;
use Depotwarehouse\Jeopardy\Participant\Contestant;
use Illuminate\Support\Collection;

class Board
{

    /**
     * A collection of contestants.
     * @var Collection
     */
    protected $contestants;
    /**
     * A collection of Categories.
     * @var Collection
     */
    protected $categories;

    /**
     * Our clue for final Jeopardy.
     * @var FinalJeopardy\State
     */
    protected $finalJeopardyState;

    /**
     * The buzzer resolver which resolves who won a particular buzz.
     * @var Resolver
     */
    protected $resolver;

    /**
     * @param Contestant[]|Collection $contestants
     * @param Category[]|Collection $categories
     * @param Resolver $resolver
     * @param BuzzerStatus $buzzerStatus
     * @param FinalJeopardy\State $final
     */
    function __construct($contestants, $categories, Resolver $resolver, BuzzerStatus $buzzerStatus, FinalJeopardy\State $final)
    {
        $this->contestants = ($contestants instanceof Collection) ? $contestants : new Collection($contestants);
        $this->categories = new Collection($categories);
        $this->resolver = $resolver;
        $this->buzzerStatus = $buzzerStatus;
        $this->finalJeopardyState = $final;
    }

    /**
     * @return BuzzerStatus
     */
    public function getBuzzerStatus()
    {
        return $this->buzzerStatus;
    }

    /**
     * @param BuzzerStatus $buzzerStatus
     * @return $this
     */
    public function setBuzzerStatus(BuzzerStatus $buzzerStatus)
    {
        $this->buzzerStatus = $buzzerStatus;
        return $this;
    }

    /**
     * The current status of the buzzer.
     * @var BuzzerStatus
     */
    protected $buzzerStatus;

    /**
     * @return mixed
     */
    public function getResolver()
    {
        return $this->resolver;
    }

    /**
     * Resolves the current buzzer competition and returns the resolution.
     * As a side-effect, this will also disable the buzzer.
     *
     * @return \Depotwarehouse\Jeopardy\Buzzer\BuzzerResolution
     */
    public function resolveBuzzes()
    {
        $resolution = $this->resolver->resolve();
        $this->buzzerStatus->disable();
        return $resolution;
    }

    /**
     * @param Contestant $contestant
     * @param $value
     * @return Contestant
     */
    public function addScore(Contestant $contestant, $value)
    {
        /** @var Contestant $c */
        $c = $this->getContestants()->first(function($key, Contestant $c) use ($contestant) {
            return $c->getName() == $contestant->getName();
        });

        if ($c == null) {
            //TODO logging.
            echo "Unable to find contestant with name {$contestant->getName()}";
        }

        $c->addScore($value);
        return $contestant;
    }

    /**
     * Gets the first question that matches both the category and value.
     * @param $categoryName
     * @param int $value
     * @return Question
     * @throws QuestionNotFoundException
     */
    public function getQuestionByCategoryAndValue($categoryName, $value)
    {
        //TODO what if we can't find anything? what if either of these return empty. Must throw exceptions, I suppose.

        /** @var Category $category */
        $category = $this->categories->first(function ($key, Category $category) use ($categoryName) {
            return $category->getName() == $categoryName;
        });

        if ($category == null) {
            throw new QuestionNotFoundException;
        }

        $question = $category->getQuestions()->first(function ($key, Question $question) use ($value) {
            return $question->getValue() == $value;
        });

        if ($question == null) {
            throw new QuestionNotFoundException;
        }

        return $question;
    }


    /**
     * @return Collection
     */
    public function getContestants()
    {
        return $this->contestants;
    }

    /**
     * @return Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    public function getFinalJeopardy()
    {
        return $this->finalJeopardyState;
    }

    public function getFinalJeopardyClue()
    {
        return $this->finalJeopardyState->getClue();
    }


}
