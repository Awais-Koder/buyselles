<?php

namespace App\Jobs;

use App\Imports\DigitalProductCodesImport;
use App\Models\Admin;
use App\Notifications\DigitalCodeImportCompleteNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ProcessDigitalCodeImportJob implements ShouldQueue
{
    use Queueable;

    /** Number of times the job may be attempted. */
    public int $tries = 1;

    /** Maximum number of seconds the job should run. */
    public int $timeout = 300;

    /**
     * @param  string  $filePath  Absolute path to the uploaded xlsx/csv file (stored on disk)
     * @param  string  $importedBy  Display name of the user who triggered the import
     * @param  int  $adminId  ID of the admin to notify on completion (0 = notify all super-admins)
     */
    public function __construct(
        private readonly string $filePath,
        private readonly string $importedBy = 'Admin',
        private readonly int $adminId = 0,
    ) {}

    public function handle(): void
    {
        try {
            $importer = app(DigitalProductCodesImport::class);
            Excel::import($importer, $this->filePath);

            $summary = $importer->getSummary();
        } catch (\Throwable $e) {
            Log::error('DigitalCodeImport job failed', [
                'file' => $this->filePath,
                'error' => $e->getMessage(),
            ]);

            $summary = [
                'processed' => 0,
                'skipped' => 0,
                'failed' => 0,
                'errors' => ['Job crashed: ' . $e->getMessage()],
            ];
        }

        $this->notifyAdmins($summary);

        // Clean up the temporary uploaded file
        if (file_exists($this->filePath)) {
            @unlink($this->filePath);
        }
    }

    /**
     * @param  array{processed: int, skipped: int, failed: int, errors: array<int, string>}  $summary
     */
    private function notifyAdmins(array $summary): void
    {
        $notification = new DigitalCodeImportCompleteNotification($summary, $this->importedBy);

        try {
            if ($this->adminId > 0) {
                $admin = Admin::find($this->adminId);
                if ($admin) {
                    $admin->notify($notification);

                    return;
                }
            }

            // Fallback: notify all active admins with admin_role_id = 1 (super admin)
            Admin::query()
                ->where('status', 1)
                ->where('admin_role_id', 1)
                ->get()
                ->each(fn(Admin $admin) => $admin->notify($notification));
        } catch (\Throwable $e) {
            Log::warning('DigitalCodeImport: failed to send notification email', [
                'error' => $e->getMessage(),
                'summary' => $summary,
            ]);
        }
    }
}
