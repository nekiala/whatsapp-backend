<?php

namespace App\Http\Controllers;

use App\HasInitialVariables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, HasInitialVariables;

    public function __construct()
    {
        $this->graph_version        = "v15.0";
        $this->phone_number_id      = config('app.wab_sender_phone_number_id');
        $this->access_token         = config('app.wab_token');
        $this->application_name     = config('app.name');
    }
}
