<?php

namespace App\Console\Commands;

use Database\Seeders\CategoryDisplayBlockSeeder;
use Illuminate\Console\Command;

class SeedCategoryDisplayBlocksCommand extends Command
{
    protected $signature = 'category:seed-display-blocks
                            {--category=* : Main category ID(s) to configure; omit for all main categories}
                            {--reset : Remove existing blocks for targeted categories before seeding}';

    protected $description = 'Seed default display block layout for main categories (App & Web dynamic category screens)';

    public function handle(CategoryDisplayBlockSeeder $seeder): int
    {
        $categoryIds = $this->option('category');
        $parsedIds = [];

        if (is_array($categoryIds) && $categoryIds !== []) {
            foreach ($categoryIds as $value) {
                foreach (explode(',', (string) $value) as $id) {
                    $id = (int) trim($id);
                    if ($id > 0) {
                        $parsedIds[] = $id;
                    }
                }
            }
            $parsedIds = array_values(array_unique($parsedIds));
        }

        if ($parsedIds === []) {
            $parsedIds = null;
        }

        if ($this->option('reset') && ! $this->confirm('This will delete existing display blocks for the selected categories. Continue?', true)) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        $stats = $seeder->seedCategories(
            categoryIds: $parsedIds,
            resetExisting: (bool) $this->option('reset')
        );

        if ($stats['categories'] === 0) {
            $this->warn('No main categories found to configure.');

            return self::FAILURE;
        }

        $this->info("Configured {$stats['categories']} main categor".($stats['categories'] === 1 ? 'y' : 'ies').'.');
        $this->line("  Blocks created: {$stats['blocks_created']}");
        $this->line("  Blocks updated: {$stats['blocks_updated']}");

        return self::SUCCESS;
    }
}
