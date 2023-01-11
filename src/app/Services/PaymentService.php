<?php

namespace App\Services;

use App\Enums\PaymentMethodItem;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Http;
use Log;

class PaymentService
{
    private string $token;
    private string $transaction_code;
    private string $callback_url;
    private string $phone_number;

    public function __construct(private readonly Customer $customer, private readonly Service $service)
    {
        $this->prepare();
    }

    private function prepare(): void
    {
        $this->phone_number = $this->customer->phone_number;
        $this->transaction_code = uniqid(sprintf('%d_', $this->customer->id), true);
        $this->token = config('app.gtw_token');
        $this->callback_url = sprintf('%s/api/payment-status/%s', config('app.url'), $this->transaction_code);
    }

    public function proceed(): bool
    {
        $phone = sprintf('0%d', substr($this->phone_number, 3));

        $proceeded = false;

        $response = Http::withToken($this->token)->contentType('application/json')
            ->post('https://gateway.ntoprog.org/api/new-transaction', [
                'phone_number' => $phone,
                'amount' => $this->service->price,
                'trans_code' => $this->transaction_code,
                'currency' => 'CDF',
                'description' => "Test WhatsApp payment",
                'language' => 'FR',
                'operation' => 'c2b',
                'email' => 'kiala@ntoprog.org',
                'callback_url' => $this->callback_url,
                'output' => 1,
                'card_payment' => 0,
                'return_url' => null,
            ]);

        if ($response->status() == 200) {

            if ($body = json_decode($response->body())) {

                if ($body->result == 'success') {

                    $payment = new Payment;

                    $payment->amount = $this->service->price;
                    $payment->service_id = $this->service->id;
                    $payment->transaction_code = $this->transaction_code;

                    $this->customer->payments()->save($payment);

                    $proceeded = true;
                }
            }
        }

        return $proceeded;
    }
}
