<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportTicketNotifyAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly int $ticketId,
        public readonly bool $isNewTicket = true,
    ) {}

    public function build(): self
    {
        $subject = $this->isNewTicket
            ? translate('New_Support_Ticket_Submitted')
            : translate('Customer_Replied_To_Support_Ticket');

        return $this->subject($subject)
            ->view('email-templates.support-ticket-notify-admin', [
                'ticketId' => $this->ticketId,
                'isNewTicket' => $this->isNewTicket,
            ]);
    }
}
