<?php

namespace Depotwarehouse\Jeopardy\Buzzer;

use Depotwarehouse\Jeopardy\Participant\Contestant;

class BuzzerResolution
{

    /** @var  Contestant */
    protected $contestant;
    /**
     * The amount of time, in milliseconds, since buzzing has opened until the user buzzed in.
     *
     * @var int
     */
    protected $time;

    public function hasWinner()
    {
        return $this->contestant !== null;
    }

    public function getContestant()
    {
        return $this->contestant;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function toJson()
    {
        $response = [ ];

        if ($this->hasWinner()) {
            $response = [
                'success' => true,
                'contestant' => $this->contestant->getName(),
                'time' => $this->getTime()
            ];
        } else {
            $response = [
                'success' => false
            ];
        }

        return json_encode($response);
    }


    public static function createSuccess(Contestant $contestant, $time)
    {
        $resolution = new BuzzerResolution();
        $resolution->contestant = $contestant;
        $resolution->time = $time;
        return $resolution;
    }

    public static function createFailure()
    {
        $resolution = new BuzzerResolution();
        return $resolution;
    }

}
