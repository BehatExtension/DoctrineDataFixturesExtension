<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace BehatExtension\DoctrineDataFixturesExtension\Service;

use BehatExtension\DoctrineDataFixturesExtension\Service\Backup\BackupInterface;
use Doctrine\DBAL\Connection;

/**
 * Data Backup Service.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class BackupService
{
    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var BackupInterface[]
     */
    private $platformBackupMap;

    /**
     * @param string $cacheDir
     */
    public function setCacheDir(string $cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * @param BackupInterface $backup
     */
    public function addBackupService(BackupInterface $backup)
    {
        $this->platformBackupMap[$backup->name()] = $backup;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasBackupService(string $name): bool
    {
        return array_key_exists($name, $this->platformBackupMap);
    }

    /**
     * @param string $name
     *
     * @return BackupInterface
     */
    public function getBackupService(string $name): BackupInterface
    {
        if (!$this->hasBackupService($name)) {
            throw new \RuntimeException('Unsupported platform '.$name);
        }

        return $this->platformBackupMap[$name];
    }

    /**
     * Returns absolute path to backup file.
     *
     * @param string $hash
     *
     * @return string
     */
    public function getBackupFile(string $hash): string
    {
        return $this->cacheDir.DIRECTORY_SEPARATOR.'test_'.$hash;
    }

    /**
     * Check if there is a backup.
     *
     * @param string $hash
     *
     * @return bool
     */
    public function hasBackup(string $hash): bool
    {
        return file_exists($this->getBackupFile($hash));
    }

    /**
     * Create a backup for the given connection / hash.
     *
     * @param Connection $connection
     * @param string     $hash
     */
    public function createBackup(Connection $connection, string $hash)
    {
        $platformName = $connection->getDatabasePlatform()->getName();
        $filename = $this->getBackupFile($hash);
        $database = $connection->getDatabase();
        $params = $connection->getParams();

        $this->getBackupService($platformName)->create($database, $filename, $params);
    }

    /**
     * Restore the backup for the given connection / hash.
     *
     * @param Connection $connection
     * @param string     $hash
     */
    public function restoreBackup(Connection $connection, string $hash)
    {
        $platformName = $connection->getDatabasePlatform()->getName();
        $filename = $this->getBackupFile($hash);
        $database = $connection->getDatabase();
        $params = $connection->getParams();

        $this->getBackupService($platformName)->restore($database, $filename, $params);
    }
}
