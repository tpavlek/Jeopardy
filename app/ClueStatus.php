<?php

namespace App;

enum ClueStatus: string
{

    case Available = 'available';
    case Revealed = 'revealed';
    case Unavailable = 'unavailable';
}
