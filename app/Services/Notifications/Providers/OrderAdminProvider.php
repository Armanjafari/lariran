<?php

namespace App\Services\Notifications\Providers;

use App\Services\Notifications\Providers\Contracts\Provider;
use \GuzzleHttp\Client;
class OrderAdminProvider implements Provider{

    private $price;
    public function __construct($price)
    {
        $this->price = $price;
    }
    public function send()
    {  
        $client = new Client(['headers' => ['Authorization' =>'AccessKey ' . config('services.sms.farazsms.api_key')],
        'json' => [
            'pattern_code' => 'ksa9g23j1bej3fd',
            'originator' => '+983000505',
            'recipient' => '+989177375015',
            'values' => [
                'price' => (string)$this->price,
            ]],
        'base_uri' => 'http://rest.ippanel.com/v1/messages/patterns/send']);
        $response = $client->request('POST');

    }
}
?>