<?php

namespace App\Services\Notifications\Providers;

use App\Services\Notifications\Providers\Contracts\Provider;
use \GuzzleHttp\Client;
class OrderUserProvider implements Provider{

    private $phone_number;
    private $order;
    public function __construct($phone_number,$order)
    {
        $this->phone_number = $phone_number;
        $this->order = $order;
    }
    public function send()
    {  
        $client = new Client(['headers' => ['Authorization' =>'AccessKey ' . config('services.sms.farazsms.api_key')],
        'json' => [
            'pattern_code' => 'x0oawsfg01x5b82',
            'originator' => '+983000505',
            'recipient' => $this->phone_number,
            'values' => [
                'order' => (string)$this->order,
            ]],
        'base_uri' => 'http://rest.ippanel.com/v1/messages/patterns/send']);
        $response = $client->request('POST');

    }
}
?>