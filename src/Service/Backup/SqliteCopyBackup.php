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

/**
 * Sqlite copy backup.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
final class SqliteCopyBackup implements BackupInterface
{
    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return 'sqlite';
    }

    /**
     * Get path to .db file.
     *
     * @param array $params
     *
     * @return string
     */
    private function getDatabaseFile(array $params): string
    {
        if (!isset($params['path'])) {
            throw new \RuntimeException('Invalid sqlite path config');
        }

        return $params['path'];
    }

    /**
     * Makes a copy of the file source to dest.
     *
     * @param string $source
     * @param string $dest
     */
    private function copy(string $source, string $dest)
    {
        if (!copy($source, $dest)) {
            throw new \RuntimeException("Unable to copy '$source' to '$dest'");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $database, string $file, array $params): void
    {
        $this->copy($this->getDatabaseFile($params), $file);
    }

    /**
     * {@inheritdoc}
     */
    public function restore(string $database, string $file, array $params): void
    {
        $this->copy($file, $this->getDatabaseFile($params));
    }
}
