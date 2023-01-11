<?php

namespace App\Services;

use App\Enums\SessionStepEnum;
use App\Models\Session;
use Illuminate\Database\Eloquent\Model;

class SessionService
{
    public static function getCustomerSession(string $phone_number): Session
    {
        $session = Session::wherePhoneNumber($phone_number)->first();

        if (!$session instanceof Model) {

            $session = new Session;

            $session->phone_number = $phone_number;
            $session->current_step = SessionStepEnum::INITIALIZATION->value;

            $session->save();
        }

        return $session;
    }
}
