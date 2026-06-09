<?php

namespace App\Http\Controllers;

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
}
