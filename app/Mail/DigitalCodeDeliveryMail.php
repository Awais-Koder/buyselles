<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sends decrypted digital product codes to the customer after a successful payment.
 *
 * $data shape:
 * [
 *   'subject'     => string,
 *   'customerName'=> string,
 *   'orderId'     => int,
 *   'orderDate'   => string,
 *   'codes'       => [
 *     ['productName' => string, 'code' => string, 'serial' => ?string, 'expiry' => ?string],
 *     ...
 *   ],
 * ]
 */
class DigitalCodeDeliveryMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public readonly array $data) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->data['subject'] ?? translate('Your Digital Product Codes'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email-templates.digital-code-delivery',
            with: ['data' => $this->data],
        );
    }
}
