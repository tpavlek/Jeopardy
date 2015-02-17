<?php

namespace Depotwarehouse\Jeopardy\Board;

class Board
{

    public static function fromJson($json)
    {
        $values = json_decode($json);

    }
}
