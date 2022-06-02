<?php

namespace App\Services\Notifications\Providers;

use App\Models\Code;
use App\Services\Notifications\Providers\Contracts\Provider;
use \GuzzleHttp\Client;
class SmsProvider implements Provider{

    private $phone_number;
    private $code;
    public function __construct($phone_number)
    {
        $this->phone_number = $phone_number;
        $this->code = mt_rand(10000,99999);
    }
    public function send()
    {  
        Code::create([
            'code' => $this->code,
            'phone_number' => $this->phone_number,
            'expired_at' => now()->addMinutes(2)]);
        $client = new Client(['headers' => ['Authorization' =>'AccessKey ' . config('services.sms.farazsms.api_key')],
        'json' => [
            'pattern_code' => 'hlb8yrj2quumd04',
            'originator' => '+983000505',
            'recipient' => $this->phone_number,
            'values' => [
                'OTP' => (string)$this->code,
            ]],
        'base_uri' => 'http://rest.ippanel.com/v1/messages/patterns/send']);
        $response = $client->request('POST');

    }
}
?>