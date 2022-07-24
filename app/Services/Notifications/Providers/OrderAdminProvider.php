<?php

namespace App\Services\Notifications\Providers;

use App\Services\Notifications\Providers\Contracts\Provider;
use \GuzzleHttp\Client;
class OrderAdminProvider implements Provider{

    private $price;
    private $phone_number;
    public function __construct($price,$phone_number)
    {
        $this->price = $price;
        $this->phone_number = $phone_number;
    }
    public function send()
    {  
        $client = new Client(['headers' => ['Authorization' =>'AccessKey ' . config('services.sms.farazsms.api_key')],
        'json' => [
            'pattern_code' => 'ksa9g23j1bej3fd',
            'originator' => '+983000505',
            'recipient' => $this->phone_number,
            'values' => [
                'price' => (string)$this->price,
            ]],
        'base_uri' => 'http://rest.ippanel.com/v1/messages/patterns/send']);
        $response = $client->request('POST');

    }
}
?>