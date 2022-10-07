<?php

namespace App;

enum BuzzerStatus: string
{

    case Open = "open";
    case Closed = "closed";
    case Buzzing = "buzzing";

}
