<?php

namespace App\Jobs\v1;

use App\Services\Notifications\Providers\SmsProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $phone_number;
    public function __construct(int $phone_number)
    {
        $this->phone_number = $phone_number;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $notif = new SmsProvider($this->phone_number);
        $notif->send();
    }
}
