<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    protected $mail_to;

    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mail_to, $data)
    {
        $this->mail_to = $mail_to;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->mail_to)->send($this->data);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('SendEmailJob failed: '.$e->getMessage());
        }
    }
}
