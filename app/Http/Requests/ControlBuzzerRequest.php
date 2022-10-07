<?php

namespace App\Http\Requests;

use App\BuzzerStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ControlBuzzerRequest extends GameMasterActionRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'status' => [ 'required', new Enum(BuzzerStatus::class) ]
        ];
    }

    public function buzzerStatus(): BuzzerStatus
    {
        return BuzzerStatus::from($this->validated('status'));
    }
}
