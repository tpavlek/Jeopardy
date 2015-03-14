<?php

namespace Depotwarehouse\Jeopardy\Board;

use Depotwarehouse\Jeopardy\Buzzer\BuzzerStatus;
use Depotwarehouse\Jeopardy\Buzzer\Resolver;
use Depotwarehouse\Jeopardy\Participant\Contestant;
use Depotwarehouse\Jeopardy\Participant\ContestantFactory;

class BoardFactory
{

    /**
     * @return Board
     */
    public static function initialize()
    {

        $json = (file_exists('game_data/questions-active.json')) ? file_get_contents('game_data/questions-active.json') : file_get_contents('game_data/questions.json');

        return static::fromJson($json);
    }

    /**
     * @param string $json
     * @return Board
     */
    public static function fromJson($json)
    {
        $contestantFactory = new ContestantFactory();

        $values = json_decode($json);
        $contestants = array_map(
            [ $contestantFactory, 'createFromObject' ],
            $values->contestants
        );

        $categories = array_map(
            function (\stdClass $category) {
                return new Category(
                    $category->name,
                    array_map(
                        function (\stdClass $question) {
                            $questionObj = new Question(
                                new Clue($question->clue),
                                new Answer($question->answer),
                                $question->value,
                                (isset($question->daily_double)) ? $question->daily_double : false
                            );
                            if ($questionObj->getClue() == null || (isset($question->used) && $question->used)) {
                                $questionObj->setUsed(true);
                            }
                            return $questionObj;
                        },
                        $category->questions
                    )
                );
            }, $values->categories
        );

        $board = new Board(
            $contestants,
            $categories,
            new Resolver(),
            new BuzzerStatus()
        );

        return $board;
    }
}
