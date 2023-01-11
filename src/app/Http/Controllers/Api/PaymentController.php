<?php

namespace App\Http\Controllers\Api;

use Adrii\Whatsapp\Whatsapp;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Exception;

class PaymentController extends Controller
{
    /**
     * @throws Exception
     */
    public function callback(string $id)
    {
        if ($payment = Payment::whereTransactionCode($id)->first()) {

            $result = json_decode(file_get_contents('php://input'));

            if ($result->result == "success") {

                $payment->enabled = true;

            }

            $payment->gwt_response  = $result->result;

            $payment->save();

            // send notification to customer
            $customer = $payment->customer;

            if ($result->result == "success") {

                $text = sprintf(
                    'Merci %s ! Nous avons reçu votre paiement de %d CDF !',
                    $customer->name,
                    $payment->amount
                );

            } else {

                $text = sprintf(
                    '%s, votre paiement de %d CDF a échoué. Veuillez reprendre.',
                    $customer->name,
                    $payment->amount
                );

            }

            $ws = new Whatsapp(
                $this->phone_number_id,
                $this->access_token,
                $this->graph_version
            );

            $ws->send_message()->text($text, $customer->phone_number);
        }
    }
}
