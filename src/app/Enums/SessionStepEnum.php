<?php

namespace App\Enums;

enum SessionStepEnum: string
{
    case INITIALIZATION = 'initialization';
    case SERVICE_SELECTED = 'service_selected';
    case SESSION_COMPLETED = 'session_completed';
}
