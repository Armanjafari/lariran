<?php

namespace App\Services\Notifications\Providers;

use App\Services\Notifications\Providers\Contracts\Provider;
use \GuzzleHttp\Client;

class OrderSuccessProvider implements Provider
{
    // TODO old version
    private $phone_number;
    private $orderid;
    private $name;
    public function __construct($phone_number, $orderid, $name)
    {
        $this->phone_number = $phone_number;
        $this->orderid = $orderid;
        $this->name = $name;
    }
    public function send()
    {
        $client = new Client([
            'headers' => ['Authorization' => 'AccessKey ' . config('services.sms.farazsms.api_key')],
            'json' => [
                'pattern_code' => 'uif29e9heq6ioc3',
                'originator' => '+983000505',
                'recipient' => $this->phone_number,
                'values' => [
                    'name' => (string)$this->name,
                    'orderid' => (string)$this->orderid,
                ]
            ],
            'base_uri' => 'http://rest.ippanel.com/v1/messages/patterns/send'
        ]);
        $response = $client->request('POST');
    }
}
