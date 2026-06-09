<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class BackupService
{
    /**
     * Buat backup database SQLite.
     * Copy file database ke folder backup dengan timestamp.
     */
    public function createBackup(): string
    {
        // Pastikan WAL mode di-checkpoint sebelum backup untuk konsistensi data
        DB::statement('PRAGMA wal_checkpoint(TRUNCATE);');

        $sourceDb = database_path('database.sqlite');
        $backupDir = storage_path('app/backups');

        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupFile = "{$backupDir}/backup_{$timestamp}.sqlite";

        File::copy($sourceDb, $backupFile);

        return $backupFile;
    }

    /**
     * Dapatkan daftar backup yang ada.
     */
    public function listBackups(): array
    {
        $backupDir = storage_path('app/backups');

        if (!File::isDirectory($backupDir)) {
            return [];
        }

        return collect(File::files($backupDir))
            ->filter(fn ($file) => $file->getExtension() === 'sqlite')
            ->map(fn ($file) => [
                'filename' => $file->getFilename(),
                'size' => round($file->getSize() / 1024, 1), // KB
                'created_at_raw' => $file->getMTime(),
                'created_at' => date('d M Y, H:i', $file->getMTime()),
                'path' => $file->getPathname(),
            ])
            ->sortByDesc('created_at_raw')
            ->values()
            ->toArray();
    }
}
