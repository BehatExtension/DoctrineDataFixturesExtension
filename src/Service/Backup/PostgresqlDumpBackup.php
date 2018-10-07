<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace BehatExtension\DoctrineDataFixturesExtension\Service\Backup;

use Symfony\Component\Process\Process;

/**
 * Mysql dump backup.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
final class PostgresqlDumpBackup implements BackupInterface
{
    private $pgRestore = 'pg_restore';

    private $pgDump = 'pg_dump';

    public function name(): string
    {
        return 'postgresql';
    }

    private function runCommand(string $command): ?int
    {
        $process = new Process($command);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return $process->getExitCode();
    }

    public function create(string $database, string $file, array $params): void
    {
        $options = '';

        if (isset($params['host']) && \mb_strlen($params['host'])) {
            $options .= sprintf(' --host=%s', escapeshellarg($params['host']));
        }

        if (isset($params['user']) && \mb_strlen($params['user'])) {
            $options .= sprintf(' --username=%s', escapeshellarg($params['user']));
        }

        if (isset($params['port'])) {
            $options .= sprintf(' --port=%s', escapeshellarg($params['port']));
        }

        $command = sprintf(
            '%s -Fc %s %s > %s',
            $this->pgDump,
            $options,
            escapeshellarg($database),
            escapeshellarg($file)
        );

        if (isset($params['password']) && \mb_strlen($params['password'])) {
            $command = sprintf('PGPASSWORD=%s ', escapeshellarg($params['password'])).$command;
        }

        $this->runCommand($command);
    }

    public function restore(string $database, string $file, array $params): void
    {
        $options = '';

        if (isset($params['host']) && \mb_strlen($params['host'])) {
            $options .= sprintf(' --host=%s', escapeshellarg($params['host']));
        }

        if (isset($params['user']) && \mb_strlen($params['user'])) {
            $options .= sprintf(' --username=%s', escapeshellarg($params['user']));
        }

        if (isset($params['port'])) {
            $options .= sprintf(' --port=%s', escapeshellarg($params['port']));
        }

        $command = sprintf(
            '%s --clean %s --dbname=%s %s',
            $this->pgRestore,
            $options,
            escapeshellarg($database),
            escapeshellarg($file)
        );

        if (isset($params['password']) && \mb_strlen($params['password'])) {
            $command = sprintf('PGPASSWORD=%s ', escapeshellarg($params['password'])).$command;
        }

        $this->runCommand($command);
    }
}
