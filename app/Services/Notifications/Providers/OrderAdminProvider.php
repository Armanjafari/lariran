<?php

namespace App\Services\Notifications\Providers;

use App\Services\Notifications\Providers\Contracts\Provider;
use \GuzzleHttp\Client;
use Morilog\Jalali\Jalalian;

class OrderAdminProvider implements Provider{

    private $price;
    private $phone_number;
    private $date;
    private $orderid;
    public function __construct($price,$phone_number ,$orderid)
    {
        $this->price = number_format($price);
        $this->date = Jalalian::now()->format('%A, %d %B %y');
        $this->orderid = $orderid;
        $this->phone_number = $phone_number;
    }
    public function send()
    {  
        $client = new Client(['headers' => ['Authorization' =>'AccessKey ' . config('services.sms.farazsms.api_key')],
        'json' => [
            'pattern_code' => '5xwu59auu6r19wk',
            'originator' => '+983000505',
            'recipient' => $this->phone_number,
            'values' => [
                'price' => (string)$this->price,
                'orderid' => (string)$this->orderid,
                'date' => (string)$this->date,
            ]],
        'base_uri' => 'http://rest.ippanel.com/v1/messages/patterns/send']);
        $response = $client->request('POST');

    }
}
?>