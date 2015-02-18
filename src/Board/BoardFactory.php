<?php

namespace Depotwarehouse\Jeopardy\Board;

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

        $board = new Board(
            array_map(
                function (\stdClass $contestant) {
                    return new Contestant($contestant->name);
                },
                $values->contestants
            ),
            array_map(
                function (\stdClass $category) {
                    return new Category(
                        $category->name,
                        array_map(
                            function (\stdClass $question) {
                                return new Question(
                                    new Clue($question->clue),
                                    new Answer($question->answer),
                                    $question->value);
                            },
                            $category->questions
                        )
                    );
                }, $values->categories
            )
        );

        return $board;
    }
}
