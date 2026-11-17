<?php

namespace App\Http\Controllers;

use App\Services\TelegramNotifier;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    private function backupDir(): string
    {
        $dir = config('database.backup_path');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    public function index()
    {
        $files = collect(glob($this->backupDir() . '/backup-*.sql'))
            ->map(fn ($f) => [
                'name' => basename($f),
                'size' => round(filesize($f) / 1024, 1) . ' KB',
                'date' => date('Y-m-d H:i:s', filemtime($f)),
            ])
            ->sortByDesc('date')
            ->values();

        return view('backup.index', compact('files'));
    }

    public function store()
    {
        $dir = $this->backupDir();

        $filename = 'backup-' . now()->format('Y-m-d_H-i-s') . '.sql';
        $path = $dir . '/' . $filename;
        $db = config('database.connections.mysql');

        $cmd = [
            config('database.mysqldump_path'),
            '--user=' . $db['username'],
            '--host=' . $db['host'],
            '--port=' . $db['port'],
        ];

        if (!empty($db['password'])) {
            $cmd[] = '--password=' . $db['password'];
        }

        $cmd[] = '--result-file=' . $path;
        $cmd[] = $db['database'];

        $process = new Process(
            $cmd,
            null,
            array_merge($_SERVER, $_ENV, [
                'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
                'windir'     => getenv('windir') ?: 'C:\\Windows',
            ])
        );
        $process->setTimeout(300);
        $process->run();

        $user = auth()->user()->name ?? 'unknown';
        $time = now()->format('Y-m-d H:i:s A');

        if (!$process->isSuccessful()) {
            // remove empty/partial file left by a failed dump
            if (file_exists($path)) {
                @unlink($path);
            }

            // never leak the DB password into Telegram or the page
            $error = $process->getErrorOutput();
            if (!empty($db['password'])) {
                $error = str_replace($db['password'], '******', $error);
            }

            TelegramNotifier::send(
                "❌ <b>Database backup FAILED</b>\n" .
                "Database Name: <code>" . e($db['database']) . "</code>\n" .
                "Backup By: " . e($user) . "\n" .
                "Error: " . e(\Illuminate\Support\Str::limit($error, 500)) . "\n" .
                "Date/Time: {$time}"
            );

            return back()->with('error', 'Backup failed: ' . $error);
        }

        $size = round(filesize($path) / 1024, 1) . ' KB';

        TelegramNotifier::send(
            "✅ <b>Database backup completed</b>\n" .
            "Database Name: <code>" . e($db['database']) . "</code>\n" .
            "File Name: <code>" . e($filename) . "</code>\n" .
            "File Size: {$size}\n" .
            "Backup By: " . e($user) . "\n" .
            "Date/Time: {$time}"
        );

        return redirect()->route('backup.index')
            ->with('success', 'Backup saved to ' . $dir . '\\' . $filename);
    }

    public function download($file)
    {
        $path = $this->backupDir() . '/' . basename($file);
        abort_unless(file_exists($path), 404);

        TelegramNotifier::send(
            "⬇️ <b>Backup downloaded</b>\n" .
            "File Name: <code>" . e(basename($file)) . "</code>\n" .
            "Download By: " . e(auth()->user()->name ?? 'unknown') . "\n" .
            "Date/Time: " . now()->format('Y-m-d H:i:s A')
        );

        return response()->download($path);
    }

    public function deleteSelected(\Illuminate\Http\Request $request)
    {
        $names = $request->input('files', []);
        $count = 0;
        $deleted = [];

        foreach ($names as $name) {
            $path = $this->backupDir() . '/' . basename($name);

            if (str_starts_with(basename($name), 'backup-')
                && str_ends_with($name, '.sql')
                && file_exists($path)) {
                @unlink($path);
                $deleted[] = basename($name);
                $count++;
            }
        }

        if ($count > 0) {
            TelegramNotifier::send(
                "🗑 <b>{$count} backup file(s) deleted</b>\n" .
                e(implode("\n", $deleted)) . "\n" .
                "Deleted By: " . e(auth()->user()->name ?? 'unknown') . "\n" .
                "Date/Time: " . now()->format('Y-m-d H:i:s A')
            );
        }

        return back()->with('success', $count . ' backup file(s) deleted.');
    }

    public function clear()
    {
        $files = glob($this->backupDir() . '/backup-*.sql');

        foreach ($files as $file) {
            @unlink($file);
        }

        if (count($files) > 0) {
            TelegramNotifier::send(
                "🗑 <b>All backups cleared (" . count($files) . " files)</b>\n" .
                "Clear By: " . e(auth()->user()->name ?? 'unknown') . "\n" .
                "Date/Time: " . now()->format('Y-m-d H:i:s')
            );
        }

        return back()->with('success', count($files) . ' backup file(s) deleted.');
    }
}
