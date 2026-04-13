<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        $backupPath = storage_path('backups');
        
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $files = scandir($backupPath);
        $backups = array_filter($files, function ($file) {
            return $file !== '.' && $file !== '..' && (substr($file, -4) === '.sql' || substr($file, -7) === '.sql.gz');
        });

        $backupDetails = collect($backups)->map(function ($file) use ($backupPath) {
            $filePath = $backupPath . '/' . $file;
            return [
                'filename' => $file,
                'size' => round(filesize($filePath) / 1024 / 1024, 2), // Size in MB
                'modified' => date('Y-m-d H:i:s', filemtime($filePath)),
                'path' => $filePath
            ];
        })->sortByDesc('modified')->values();

        return view('admin.backups.index', compact('backupDetails'));
    }

    public function create()
    {
        // Create a new backup
        $exitCode = Artisan::call('backup:database');
        
        if ($exitCode === 0) {
            return redirect()->route('admin.backups.index')->with('success', 'Backup created successfully!');
        } else {
            return redirect()->route('admin.backups.index')->with('error', 'Failed to create backup.');
        }
    }

    public function download($filename)
    {
        $path = storage_path('backups/' . $filename);
        
        if (!file_exists($path)) {
            abort(404, 'Backup file not found.');
        }

        return response()->download($path);
    }

    public function delete($filename)
    {
        $path = storage_path('backups/' . $filename);
        
        if (!file_exists($path)) {
            return redirect()->route('admin.backups.index')->with('error', 'Backup file not found.');
        }

        if (unlink($path)) {
            return redirect()->route('admin.backups.index')->with('success', 'Backup deleted successfully!');
        } else {
            return redirect()->route('admin.backups.index')->with('error', 'Failed to delete backup.');
        }
    }

    public function restore(Request $request, $filename)
    {
        $path = storage_path('backups/' . $filename);
        
        if (!file_exists($path)) {
            return redirect()->route('admin.backups.index')->with('error', 'Backup file not found.');
        }

        // Confirm restoration
        if (!$request->has('confirmed')) {
            return view('admin.backups.confirm-restore', compact('filename'));
        }

        // Perform the restoration
        $exitCode = Artisan::call('restore:database', [
            'filename' => $filename
        ]);
        
        if ($exitCode === 0) {
            return redirect()->route('admin.backups.index')->with('success', 'Database restored successfully!');
        } else {
            return redirect()->route('admin.backups.index')->with('error', 'Failed to restore database.');
        }
    }
}