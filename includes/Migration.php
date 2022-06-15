<?php

namespace LicenseManagerForWooCommerce;

defined('ABSPATH') || exit;

class Migration
{
    /**
     * @var string
     */
    const MODE_UP = 'UP';

    /**
     * @var string
     */
    const MODE_DOWN = 'DOWN';

    /**
     * Performs a database upgrade.
     *
     * @param int $oldDatabaseVersion
     */
    public static function up($oldDatabaseVersion)
    {
        $regExFileName = '/(\d{14})_(.*?)_(.*?)\.php/';
        $migrationMode = self::MODE_UP;

        foreach (glob(LMFWC_MIGRATIONS_DIR . '*.php') as $fileName) {
            if (preg_match($regExFileName, basename($fileName), $match)) {
                $fileBasename    = $match[0];
                $fileDateTime    = $match[1];
                $fileVersion     = $match[2];
                $fileDescription = $match[3];

                global $wpdb;

                if (intval($fileVersion) <= Setup::DB_VERSION
                    && intval($fileVersion) > $oldDatabaseVersion
                ) {
                    require_once $fileName;
                }
            }
        }

        update_option('lmfwc_db_version', Setup::DB_VERSION, true);
    }

    /**
     * Performs a database downgrade (Currently not in use).
     *
     * @param $oldDatabaseVersion
     */
    public static function down($oldDatabaseVersion)
    {
    }
}