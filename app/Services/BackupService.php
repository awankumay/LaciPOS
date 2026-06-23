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
     * Restore database dari file backup.
     * Mengganti database.sqlite saat ini dengan file backup yang diunggah.
     */
    public function restoreBackup($uploadedFile)
    {
        // 1. Perintahkan SQLite secara native untuk melebur dan MENGOSONGKAN (0 bytes) file WAL.
        // Ini mencegah sisa data di file WAL (seperti stok 120) tertimpa balik ke file database yang baru direstore.
        DB::statement('PRAGMA wal_checkpoint(TRUNCATE);');

        // 2. Disconnect aktifkan PDO, baru purge dari manager agar Windows melepaskan lock file.
        DB::disconnect('sqlite');
        DB::purge('sqlite');

        $dbPath = database_path('database.sqlite');
        $walPath = database_path('database.sqlite-wal');
        $shmPath = database_path('database.sqlite-shm');
        
        // 3. Hapus file WAL dan SHM secara fisik (jika masih tersisa). 
        // Menggunakan @unlink saja, JANGAN menggunakan file_put_contents karena akan merusak header SQLite dan memicu Disk I/O Error.
        if (File::exists($walPath)) {
            @unlink($walPath);
        }
        if (File::exists($shmPath)) {
            @unlink($shmPath);
        }

        // 4. Timpa file database
        if (!File::copy($uploadedFile->getRealPath(), $dbPath)) {
            throw new \Exception("Gagal menimpa database utama. Pastikan tidak ada program lain yang sedang membuka database.");
        }
        
        // 5. Reconnect
        DB::reconnect('sqlite');
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
