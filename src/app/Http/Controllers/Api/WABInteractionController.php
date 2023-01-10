<?php

namespace App\Http\Controllers\Api;

use Adrii\Whatsapp\Whatsapp;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WABInteractionController extends Controller
{
    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response|string
     */
    public function subscribe(Request $request)
    {
        if ($request->isMethod('GET')) {

            Log::debug("Got a GET Ping from Whatsapp!");

            $mode = trim($request->input('hub_mode'));
            $token = trim($request->input('hub_verify_token'));
            $challenge = trim($request->input('hub_challenge'));

            if ($token && $challenge && $mode == 'subscribe' && $token == config('app.wab_verify_token')) {

                return response($challenge);

            }

            return response(403);
        }

        return $this->interact($request);
    }

    public function interact(Request $request)
    {
        Log::debug("Got a POST Ping from Whatsapp!");

        $graph_version    = "v15.0";
        $phone_number_id  = config('app.wab_sender_phone_number_id');
        $access_token     = config('app.wab_token');
        $recipient_id     = config('app.wab_phone_number');

        try {

            $ws = new Whatsapp(
                $phone_number_id,
                $access_token,
                $graph_version
            );

            $data = $ws->getMessage($request->json());

            Log::debug($data);

        } catch (Exception $exception) {

            Log::debug($exception->getMessage());
        }

        return "It's okay";
    }

    public function send(Request $request)
    {
        Log::debug("Sending a POST message!");

        $graph_version    = "v15.0";
        $phone_number_id  = config('app.wab_sender_phone_number_id');
        $access_token     = config('app.wab_token');
        $recipient_id     = "243822178836";

        try {

            $ws = new Whatsapp(
                $phone_number_id,
                $access_token,
                $graph_version
            );

            $button = [
                "header" => "Header",
                "body"   => "Body",
                "footer" => "Footer",
                "action" => [
                    "buttons" => [
                        [
                            "type" => "reply",
                            "reply" => [
                                "id" => "UNIQUE_BUTTON_ID_1",
                                "title" => "Kiala Ntona"
                            ]
                        ],
                        [
                            "type" => "reply",
                            "reply" => [
                                "id" => "UNIQUE_BUTTON_ID_2",
                                "title" => "Ntona Kiala"
                            ]
                        ]
                    ]
                ]
            ];

            $ws->send_message()->interactive($button, $recipient_id, "button");

        } catch (Exception $exception) {

            Log::debug($exception->getMessage());
        }

        return "It's okay";
    }
}
