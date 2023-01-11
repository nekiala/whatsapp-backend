<?php

namespace App\Http\Controllers\Api;

use Adrii\Whatsapp\Whatsapp;
use App\Enums\SessionStepEnum;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Service;
use App\Services\PaymentService;
use App\Services\SessionService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Model;
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
        if ($entry = $request->input('entry')) {

            $value = $entry[0]['changes'][0]['value'];


            if (isset($value['messages'])) {

                //Log::debug($value);

                $messages = $value['messages'][0];

                switch ($messages['type']) {

                    case 'text':
                        $contacts = $value['contacts'][0];

                        //Log::debug("It's a text message!");

                        try {

                            $this->sendWelcomeMessage($contacts);

                        } catch (Exception $exception) {

                            Log::debug($exception->getMessage());
                        }
                        break;

                    case 'interactive':

                        try {

                            $this->interactWithUser($messages);

                        } catch (Exception $exception) {

                            Log::debug($exception->getMessage());
                        }
                        break;
                }

            } else {

                Log::debug($entry);
            }
        }

        return "It's okay";
    }

    /**
     * @throws Exception
     */
    private function sendWelcomeMessage(array $contacts)
    {
        $ws = new Whatsapp(
            $this->phone_number_id,
            $this->access_token,
            $this->graph_version
        );

        $header_text = sprintf(
            'Bienvenue %s !',
            $contacts['profile']['name']
        );

        $recipient_id = $contacts['wa_id'];

        $customer = Customer::wherePhoneNumber($recipient_id)->first();

        // if customer doesn't exist, let's create him
        if (!$customer instanceof Model) {

            $customer = new Customer;

            $customer->phone_number = $recipient_id;

        }

        $customer->name = $contacts['profile']['name'];

        $customer->save();

        $session = SessionService::getCustomerSession($recipient_id);

        if ( !$session->current_step == SessionStepEnum::INITIALIZATION->value ) {

            $session->delete();
        }

        // get all services
        $services = Service::all();
        $rows = [];

        foreach ($services as $service) {

            $rows[] = [
                "id" => sprintf('ser_%d', $service->id),
                "title" => substr($service->name, 0, 20),
                "description" => sprintf('%d CDF', intval($service->price))
            ];
        }

        $list = [
            "header" => $header_text,
            "body" => "Choisissez le service ce que vous voulez payer. Merci d'avance !",
            "action" => [
                "button" => "Choisissez",
                "sections" => [
                    [
                        "title" => "Payer en CDF",
                        "rows" => $rows
                    ]
                ]
            ]
        ];

        $ws->send_message()->interactive($list, $recipient_id);

    }

    /**
     * @throws Exception
     */
    private function interactWithUser(array $messages)
    {
        //Log::debug("It's an interactive message!");

        $ws = new Whatsapp(
            $this->phone_number_id,
            $this->access_token,
            $this->graph_version
        );

        $recipient_id = $messages['from'];

        $customer = Customer::wherePhoneNumber($recipient_id)->first();

        if ($customer instanceof Model) {

            // check if it's a list_replay or a button_reply

            if (isset($messages['interactive']['list_reply'])) {

                $description = $messages['interactive']['list_reply']['description'];
                $service_id = $messages['interactive']['list_reply']['id'];

                $body_text = sprintf("Vous payez les %s via quelle méthode ?", $description);

                $button = [
                    "header" => "Méthode de paiement",
                    "body" => $body_text,
                    "action" => [
                        "buttons" => [
                            [
                                "type" => "reply",
                                "reply" => [
                                    "id" => "pay_vdc.$service_id",
                                    "title" => "M-PESA"
                                ]
                            ],
                            [
                                "type" => "reply",
                                "reply" => [
                                    "id" => "pay_om.$service_id",
                                    "title" => "ORANGE MONEY"
                                ]
                            ]
                        ]
                    ]
                ];

                $ws->send_message()->interactive($button, $recipient_id, "button");

            } elseif (isset($messages['interactive']['button_reply'])) {

                // get button id
                $button_id = $messages['interactive']['button_reply']['id'];

                $service_text = explode('.', $button_id)[1];
                $service_id = substr($service_text, strpos($service_text, '_') + 1);

                $service = Service::find($service_id);

                if ($service instanceof Model) {

                    $this->sendPaymentOngoingStatusText($recipient_id);

                    sleep(2);

                    $paymentService = new PaymentService($customer, $service);

                    $this->sendPaymentStatusText($paymentService->proceed(), $recipient_id);

                    $customer->session->current_step = SessionStepEnum::SESSION_COMPLETED->value;
                    $customer->session->save();

                } else {

                    Log::debug("Service not found!");
                }

            }


        } else {

            Log::debug("No information about the recipient!");
        }


    }

    /**
     * @throws Exception
     */
    private function sendPaymentStatusText(bool $proceed, string $recipient_id)
    {
        $ws = new Whatsapp(
            $this->phone_number_id,
            $this->access_token,
            $this->graph_version
        );

        $text = $proceed ? "Veuillez confirmer sur votre téléphone 🍍" : "Erreur de paiement survenue.";

        $ws->send_message()->text($text, $recipient_id);
    }

    /**
     * @throws Exception
     */
    private function sendPaymentOngoingStatusText(string $recipient_id)
    {
        $ws = new Whatsapp(
            $this->phone_number_id,
            $this->access_token,
            $this->graph_version
        );

        $text = "Traitement en cours...";

        $ws->send_message()->text($text, $recipient_id);
    }
}
