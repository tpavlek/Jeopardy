<?php

namespace Depotwarehouse\Jeopardy\Board;

use Depotwarehouse\Jeopardy\Buzzer\BuzzerStatus;
use Depotwarehouse\Jeopardy\Buzzer\Resolver;
use Depotwarehouse\Jeopardy\Participant\Contestant;

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
        $values = json_decode($json);
        $contestants = array_map(
            function (\stdClass $contestant) {
                return new Contestant($contestant->name);
            },
            $values->contestants
        );

        $categories = array_map(
            function (\stdClass $category) {
                return new Category(
                    $category->name,
                    array_map(
                        function (\stdClass $question) {
                            $question = new Question(
                                new Clue($question->clue),
                                new Answer($question->answer),
                                $question->value,
                                (isset($question->daily_double)) ? $question->daily_double : false
                            );
                            if ($question->getClue() == null) {
                                $question->setUsed(true);
                            }
                            return $question;
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
