<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImpactoBackup extends Command
{
    protected $signature = 'impacto:backup';
    protected $description = 'Genera un respaldo simple de la base de datos de IMPACTO URBANIZACIONES.';

    public function handle(): int
    {
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);
        $timestamp = now()->format('Ymd_His');
        $connection = config('database.default');

        if ($connection === 'sqlite') {
            $database = config('database.connections.sqlite.database');
            if ($database === ':memory:') {
                $target = "{$dir}/impacto_{$timestamp}_memory.txt";
                File::put($target, 'Base SQLite en memoria: no existe archivo fisico para copiar.');
                $this->warn("La base SQLite esta en memoria; se genero constancia: {$target}");
                return self::SUCCESS;
            }

            if (! $database || ! File::exists($database)) {
                $this->error('No se encontro el archivo SQLite para respaldar.');
                return self::FAILURE;
            }

            $target = "{$dir}/impacto_{$timestamp}.sqlite";
            File::copy($database, $target);
            $this->info("Backup SQLite generado: {$target}");
            return self::SUCCESS;
        }

        if ($connection === 'mysql') {
            $target = "{$dir}/impacto_{$timestamp}.sql";
            $cfg = config('database.connections.mysql');
            $cmd = sprintf(
                'mysqldump -h%s -P%s -u%s %s %s > %s',
                escapeshellarg($cfg['host']),
                escapeshellarg((string) $cfg['port']),
                escapeshellarg($cfg['username']),
                $cfg['password'] ? '-p'.escapeshellarg($cfg['password']) : '',
                escapeshellarg($cfg['database']),
                escapeshellarg($target)
            );
            exec($cmd, $output, $code);

            if ($code !== 0) {
                $this->error('No se pudo ejecutar mysqldump. Verifica que MySQL tools esten instaladas.');
                return self::FAILURE;
            }

            $this->info("Backup MySQL generado: {$target}");
            return self::SUCCESS;
        }

        $this->error("Conexion {$connection} no soportada por este backup simple.");
        return self::FAILURE;
    }
}
