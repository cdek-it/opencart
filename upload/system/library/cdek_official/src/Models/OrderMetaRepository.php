<?php

namespace CDEK\Models;

use CDEK\Helpers\LogHelper;
use CDEK\RegistrySingleton;
use DB;
use Exception;

class OrderMetaRepository
{
    private const TABLE_NAME = 'cdek_order_meta';
    private const COLUMNS
                             = [
            'id'          => 'INT(11) NOT NULL AUTO_INCREMENT',
            'order_id'    => 'INT(11) NOT NULL',
            'cdek_number' => 'VARCHAR(255) NOT NULL',
            'cdek_uuid'   => 'VARCHAR(255) NOT NULL',
            'pvz_code'    => 'VARCHAR(255) NOT NULL',
            'length'      => 'SMALLINT UNSIGNED NOT NULL',
            'width'       => 'SMALLINT UNSIGNED NOT NULL',
            'height'      => 'SMALLINT UNSIGNED NOT NULL',
            'weight'      => 'MEDIUMINT UNSIGNED NOT NULL',
            'deleted_at'  => 'TIMESTAMP NULL DEFAULT NULL',
        ];

    public static function create(): void
    {
        try {
            /** @var DB $db */
            $db = RegistrySingleton::getInstance()->get('db');

            $table       = DB_PREFIX . self::TABLE_NAME;
            $tableExists = $db->query("SHOW TABLES LIKE '$table'")->num_rows > 0;

            LogHelper::write('updating table');

            if (!$tableExists) {
                LogHelper::write('creating table');
                $db->query(
                    sprintf(
                        'CREATE TABLE `%s` (
                                            %s 
                                            PRIMARY KEY (`id`),
                                            UNIQUE KEY `order_id_unique` (`order_id`),
                                            FOREIGN KEY (`order_id`) REFERENCES `%sorder`(`order_id`) ON DELETE RESTRICT ON UPDATE CASCADE
                                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;',
                        $table,
                        array_reduce(
                            array_keys(self::COLUMNS),
                            static fn($carry, $e) => $carry . "`$e`" . self::COLUMNS[$e] . ", ",
                            '',
                        ),
                        DB_PREFIX,
                    ),
                );
            }

            $existingColumns = array_map(
                static fn($el) => $el['Field'],
                $db->query("SHOW COLUMNS FROM `$table`")->rows,
            );

            $missingColumns = array_diff(array_keys(self::COLUMNS), $existingColumns);

            foreach ($missingColumns as $column) {
                LogHelper::write('adding column ' . $column);
                $db->query(
                    sprintf(
                        'ALTER TABLE `%s` ADD COLUMN `%s` %s',
                        $table,
                        $column,
                        self::COLUMNS[$column],
                    ),
                );
            }

            $redundantColumns = array_diff($existingColumns, array_keys(self::COLUMNS));

            foreach ($redundantColumns as $column) {
                LogHelper::write('dropping column ' . $column);
                $db->query(
                    sprintf(
                        'ALTER TABLE %s DROP COLUMN %s',
                        $table,
                        $column,
                    ),
                );
            }

            LogHelper::write('finished db modifications');
        } catch (Exception $e) {
            LogHelper::write('error while updating table: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function insertCdekUuid(int $orderId, string $orderUuid): void
    {
        /** @var DB $db */
        $db = RegistrySingleton::getInstance()->get('db');

        $db->query(
            sprintf(
                'UPDATE `%s` SET `cdek_uuid`="%s", `deleted_at`=null WHERE `order_id` = %u',
                DB_PREFIX . self::TABLE_NAME,
                $orderUuid,
                $orderId,
            ),
        );
    }

    public static function insertCdekTrack(int $orderId, string $track): void
    {
        /** @var DB $db */
        $db = RegistrySingleton::getInstance()->get('db');

        $db->query(
            sprintf(
                'UPDATE `%s` SET `cdek_number`="%s" WHERE `order_id` = %u',
                DB_PREFIX . self::TABLE_NAME,
                $track,
                $orderId,
            ),
        );
    }

    public static function insertOfficeCode(int $orderId, string $officeCode): void
    {
        /** @var DB $db */
        $db = RegistrySingleton::getInstance()->get('db');

        $db->query(
            sprintf(
                'UPDATE `%s` SET `pvz_code`="%s" WHERE `order_id` = %u',
                DB_PREFIX . self::TABLE_NAME,
                $db->escape($officeCode),
                $orderId,
            ),
        );
    }

    public static function insertInitialData(
        int $orderId,
        int $height,
        int $width,
        int $length,
        int $weight
    ): void {
        /** @var DB $db */
        $db = RegistrySingleton::getInstance()->get('db');

        $db->query(
            sprintf(
                "INSERT INTO `%s` 
                                        (`order_id`, `height`, `width`, `length`, `weight`) 
                                        VALUES (%u, %u, %u, %u, %u) 
                                        ON DUPLICATE KEY UPDATE
                                        `height` = VALUES(`height`),
                                        `width` = VALUES(`width`),
                                        `length` = VALUES(`length`),
                                        `weight` = VALUES(`weight`)
                                        ",
                DB_PREFIX . self::TABLE_NAME,
                $orderId,
                $height,
                $width,
                $length,
                $weight,
            ),
        );
    }

    public static function getOrder(int $orderId): ?array
    {
        /** @var DB $db */
        $db = RegistrySingleton::getInstance()->get('db');

        $table = DB_PREFIX . self::TABLE_NAME;
        $query = $db->query("SELECT * FROM `$table` WHERE `order_id` = $orderId");

        return $query->num_rows === 0 ? null : $query->rows[0];
    }

    public static function deleteOrder(int $orderId)
    {
        /** @var DB $db */
        $db = RegistrySingleton::getInstance()->get('db');

        $table = DB_PREFIX . self::TABLE_NAME;
        $db->query(sprintf("UPDATE `%s` SET `deleted_at`=now() WHERE `order_id`='%s'", $table, $orderId));
    }
}
