<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\BackupService;
use Inertia\Inertia;

class BackupController extends Controller
{
    public function __construct(private BackupService $backupService) {}

    public function index()
    {
        return Inertia::render('Settings/Backup', [
            'backups' => $this->backupService->listBackups(),
        ]);
    }

    public function store()
    {
        try {
            $path = $this->backupService->createBackup();
            return back()->with('success', 'Backup berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    public function download(string $filename)
    {
        $path = storage_path("app/backups/{$filename}");

        if (!file_exists($path)) {
            return back()->with('error', 'File backup tidak ditemukan.');
        }

        return response()->download($path);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:51200' // max 50MB
        ]);

        $file = $request->file('backup_file');
        
        if ($file->getClientOriginalExtension() !== 'sqlite') {
            return back()->with('error', 'Format file tidak valid. Harap unggah file .sqlite');
        }

        try {
            $this->backupService->restoreBackup($file);
            return back()->with('success', 'Database berhasil di-restore dari file backup.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Restore failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal me-restore database: ' . $e->getMessage());
        }
    }
}
