<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
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
     * @var array
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
     * @param array $map
     */
    public function setPlatformBackupMap(array $map)
    {
        foreach ($map as $key => $value) {
            $this->setPlatformBackup($key, $value);
        }
    }

    /**
     * @return array
     */
    public function getPlatformBackupMap(): array
    {
        return $this->platformBackupMap;
    }

    /**
     * @param string          $platformName
     * @param BackupInterface $backup
     */
    public function setPlatformBackup(string $platformName, BackupInterface $backup)
    {
        $this->platformBackupMap[$platformName] = $backup;
    }

    /**
     * @param string $name
     *
     * @return BackupInterface
     */
    public function getPlatformBackup(string $name): BackupInterface
    {
        $map = $this->getPlatformBackupMap();
        $item = isset($map[$name]) ? $map[$name] : null;

        if ($item === null) {
            throw new \RuntimeException('Unsupported platform '.$name);
        }

        return $item;
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
    public function hasBackup($hash)
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
        $platform = $connection->getDatabasePlatform();
        $filename = $this->getBackupFile($hash);
        $database = $connection->getDatabase();
        $params = $connection->getParams();
        $platformName = $platform->getName();

        $this->getPlatformBackup($platformName)->create($database, $filename, $params);
    }

    /**
     * Restore the backup for the given connection / hash.
     *
     * @param Connection $connection
     * @param string     $hash
     */
    public function restoreBackup(Connection $connection, string $hash)
    {
        $platform = $connection->getDatabasePlatform();
        $filename = $this->getBackupFile($hash);
        $database = $connection->getDatabase();
        $params = $connection->getParams();
        $platformName = $platform->getName();

        $this->getPlatformBackup($platformName)->restore($database, $filename, $params);
    }
}
