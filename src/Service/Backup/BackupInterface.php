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
 * Backup platform interface.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
interface BackupInterface
{
    /**
     * The name of the platform supported by the backup.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Create a backup file for the given database.
     *
     * @param string $database
     * @param string $file
     * @param array  $params
     *
     * @return void
     */
    public function create(string $database, string $file, array $params): void;

    /**
     * Restore the backup file into the given database.
     *
     * @param string $database
     * @param string $file
     * @param array  $params
     *
     * @return void
     */
    public function restore(string $database, string $file, array $params): void;
}
