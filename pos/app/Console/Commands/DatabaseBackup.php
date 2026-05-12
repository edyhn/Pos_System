<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Backup the SQLite database';

    public function handle(): int
    {
        $dbPath = database_path('database.sqlite');

        if (!file_exists($dbPath)) {
            $this->error('Database file not found.');
            return Command::FAILURE;
        }

        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'backup-' . now()->format('Y-m-d-Hi') . '.sqlite';
        $backupPath = $backupDir . '/' . $filename;

        if (copy($dbPath, $backupPath)) {
            $this->info("Backup created: {$filename}");

            $this->cleanOldBackups($backupDir);

            return Command::SUCCESS;
        }

        $this->error('Backup failed.');
        return Command::FAILURE;
    }

    protected function cleanOldBackups(string $backupDir): void
    {
        $files = glob($backupDir . '/backup-*.sqlite');
        $now = now()->subDays(30);

        foreach ($files as $file) {
            if (filemtime($file) < $now->timestamp) {
                unlink($file);
                $this->info("Deleted old backup: " . basename($file));
            }
        }
    }
}
