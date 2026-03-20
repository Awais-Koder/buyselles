<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DigitalCodeImportCompleteNotification extends Notification
{
    use Queueable;

    /**
     * @param  array{processed: int, skipped: int, failed: int, errors: array<int, string>}  $summary
     * @param  string  $importedBy  Name of the admin/vendor who triggered the import
     */
    public function __construct(
        private readonly array $summary,
        private readonly string $importedBy = 'System',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Digital Code Import Completed — Buyselles')
            ->greeting('Hello, ' . $notifiable->name . '!')
            ->line('A digital product code import job has finished.')
            ->line('**Imported by:** ' . $this->importedBy)
            ->line('**Processed (saved):** ' . $this->summary['processed'])
            ->line('**Skipped (blank codes):** ' . $this->summary['skipped'])
            ->line('**Failed:** ' . $this->summary['failed']);

        if (! empty($this->summary['errors'])) {
            $message->line('**Errors:**');
            foreach (array_slice($this->summary['errors'], 0, 10) as $error) {
                $message->line('- ' . $error);
            }
            if (count($this->summary['errors']) > 10) {
                $message->line('...and ' . (count($this->summary['errors']) - 10) . ' more errors.');
            }
        }

        return $message
            ->action('Go to Digital Products', url(route('admin.products.list', ['in_house'], false)))
            ->line('Thank you for using Buyselles.');
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Digital Code Import Completed',
            'imported_by' => $this->importedBy,
            'processed' => $this->summary['processed'],
            'skipped' => $this->summary['skipped'],
            'failed' => $this->summary['failed'],
            'has_errors' => ! empty($this->summary['errors']),
            'error_count' => count($this->summary['errors']),
        ];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
