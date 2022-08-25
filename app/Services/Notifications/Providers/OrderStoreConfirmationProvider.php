<?php

namespace App\Services\Notifications\Providers;

use App\Services\Notifications\Providers\Contracts\Provider;
use \GuzzleHttp\Client;
class OrderStoreConfirmationProvider implements Provider{

    private $phone_number;
    private $name;
    private $orderid;
    public function __construct($phone_number,$orderid,$name)
    {
        $this->phone_number = $phone_number;
        $this->name = $name;
        $this->orderid = $orderid;
    }
    public function send()
    {  
        $client = new Client(['headers' => ['Authorization' =>'AccessKey ' . config('services.sms.farazsms.api_key')],
        'json' => [
            'pattern_code' => '6qv0oa9uslrj8bo',
            'originator' => '+983000505',
            'recipient' => $this->phone_number,
            'values' => [
                'name' => (string)$this->name,
                'orderid' => (string)$this->orderid,
            ]],
        'base_uri' => 'http://rest.ippanel.com/v1/messages/patterns/send']);
        $response = $client->request('POST');

    }
}
