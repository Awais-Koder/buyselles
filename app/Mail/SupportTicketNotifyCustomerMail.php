<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportTicketNotifyCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly int $ticketId,
    ) {}

    public function build(): self
    {
        return $this->subject(translate('Support_Ticket_Update'))
            ->view('email-templates.support-ticket-notify-customer', [
                'ticketId' => $this->ticketId,
            ]);
    }
}
