<?php

namespace App\Services\Notifications\Providers;

use App\Services\Notifications\Providers\Contracts\Provider;
use \GuzzleHttp\Client;
class OrderStatusProvider implements Provider{

    private $phone_number;
    private $code;
    private $status;
    public function __construct($phone_number,$status)
    {
        $this->phone_number = $phone_number;
        $this->status = $status;
    }
    public function send()
    {  
        $client = new Client(['headers' => ['Authorization' =>'AccessKey ' . config('services.sms.farazsms.api_key')],
        'json' => [
            'pattern_code' => 'kcbjbtnp0lh2i4c',
            'originator' => '+983000505',
            'recipient' => $this->phone_number,
            'values' => [
                'status' => (string)$this->status,
            ]],
        'base_uri' => 'http://rest.ippanel.com/v1/messages/patterns/send']);
        $response = $client->request('POST');

    }
}
?>