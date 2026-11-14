<?php

namespace App\Http\Controllers;

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

        if (!$process->isSuccessful()) {
            return back()->with('error', 'Backup failed: ' . $process->getErrorOutput());
        }

        return redirect()->route('backup.index')
            ->with('success', 'Backup saved to ' . $dir . '\\' . $filename);
    }

    public function download($file)
    {
        $path = $this->backupDir() . '/' . basename($file);
        abort_unless(file_exists($path), 404);
        return response()->download($path);
    }
    public function deleteSelected(\Illuminate\Http\Request $request)
    {
        $names = $request->input('files', []);
        $count = 0;

        foreach ($names as $name) {
            $path = $this->backupDir() . '/' . basename($name);

            if (str_starts_with(basename($name), 'backup-')
                && str_ends_with($name, '.sql')
                && file_exists($path)) {
                @unlink($path);
                $count++;
            }
        }

        return back()->with('success', $count . ' backup file(s) deleted.');
    }
    public function clear()
    {
        $files = glob($this->backupDir() . '/backup-*.sql');

        foreach ($files as $file) {
            @unlink($file);
        }

        return back()->with('success', count($files) . ' backup file(s) deleted.');
    }
}