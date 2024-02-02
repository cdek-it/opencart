<?php

namespace CDEK;

use DB;

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
            'deleted_at'  => 'TIMESTAMP(0) DEFAULT NULL',
        ];

    public static function create(): void
    {
        /** @var DB $db */
        $db          = RegistrySingleton::getInstance()->get('db');
        $table       = DB_PREFIX . self::TABLE_NAME;
        $tableExists = $db->query("SHOW TABLES LIKE '$table'")->num_rows > 0;

        if (!$tableExists) {
            $db->query(sprintf('CREATE TABLE %s (
                                            %s 
                                            PRIMARY KEY (`id`),
                                            UNIQUE KEY `order_id_unique` (`order_id`),
                                            FOREIGN KEY (`order_id`) REFERENCES %sorder(`order_id`) ON DELETE RESTRICT ON UPDATE CASCADE
                                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;',
                               $table,
                               array_reduce(array_keys(self::COLUMNS),
                                   static fn($carry, $e) => $carry . "`$e`" . self::COLUMNS[$e] . ", ",
                                            ''),
                               DB_PREFIX));
        }

        $existingColumns = array_map(static fn($el) => $el['Field'],
            $db->query("SHOW COLUMNS FROM `$table`")->rows);

        $missingColumns = array_diff(array_keys(self::COLUMNS), $existingColumns);

        foreach ($missingColumns as $column) {
            $db->query(sprintf('ALTER TABLE %s ADD COLUMN %s %s',
                               $table,
                               $column,
                               self::COLUMNS[$column]));
        }

        $redundantColumns = array_diff($existingColumns, array_keys(self::COLUMNS));

        foreach ($redundantColumns as $column) {
            $db->query(sprintf('ALTER TABLE %s DROP COLUMN %s',
                               $table,
                               $column));
        }
    }

    public static function insertCdekUuid(int $orderId, string $orderUuid): void
    {
        /** @var DB $db */
        $db = RegistrySingleton::getInstance()->get('db');
        $db->query(sprintf('UPDATE %s SET cdek_uuid="%s" WHERE `order_id` = %u',
                           DB_PREFIX . self::TABLE_NAME,
                           $orderUuid,
                           $orderId));
    }

    public static function insertCdekTrack(int $orderId, string $track): void
    {
        /** @var DB $db */
        $db = RegistrySingleton::getInstance()->get('db');
        $db->query(sprintf('UPDATE %s SET cdek_number="%s" WHERE `order_id` = %u',
                           DB_PREFIX . self::TABLE_NAME,
                           $track,
                           $orderId));
    }

    public static function insertInitialData(
        int $orderId,
        string $officeCode,
        int $height,
        int $width,
        int $length,
        int $weight
    ): void {
        /** @var DB $db */
        $db = RegistrySingleton::getInstance()->get('db');
        $db->query(sprintf("INSERT INTO %s 
                                        (order_id, pvz_code, height, width, length, weight) 
                                        VALUES (%u, '%s', %u, %u, %u, %u) 
                                        ON DUPLICATE KEY UPDATE
                                        pvz_code = VALUES(pvz_code)",
                           DB_PREFIX . self::TABLE_NAME,
                           $orderId,
                           $db->escape($officeCode),
                           $height,
                           $width,
                           $length,
                           $weight));
    }

    public static function getOrder(int $orderId): ?array
    {
        /** @var DB $db */
        $db    = RegistrySingleton::getInstance()->get('db');
        $table = DB_PREFIX . self::TABLE_NAME;
        $query = $db->query("SELECT * FROM `$table` WHERE `order_id` = $orderId");

        return $query->num_rows === 0 ? null : $query->rows[0];
    }

    public static function insertOrderMeta(array $data, int $orderId): void
    {
        /** @var DB $db */
        $db = RegistrySingleton::getInstance()->get('db');
        if (!is_numeric($data['cdek_number'])) {
            $data['cdek_number'] = null;
        }

        $db->query(sprintf("INSERT INTO %s
                                        (order_id, cdek_number, cdek_uuid, name, type, payment_type, to_location, created) 
                                        VALUES (%u, '%s', '%s', '%s', '%s', '%s', '%s', 1) 
                                        ON DUPLICATE KEY UPDATE 
                                            cdek_number = VALUES(cdek_number), 
                                            cdek_uuid = VALUES(cdek_uuid), 
                                            name = VALUES(name), 
                                            type = VALUES(type), 
                                            payment_type = VALUES(payment_type),
                                            to_location = VALUES(to_location),
                                            created = VALUES(created)",
                           DB_PREFIX . self::TABLE_NAME,
                           $orderId,
                           $db->escape($data['cdek_number']),
                           $db->escape($data['cdek_uuid']),
                           $db->escape($data['name']),
                           $db->escape($data['type']),
                           $db->escape($data['payment_type']),
                           $db->escape($data['to_location'])));
    }

    public static function deleteOrder(int $orderId): void
    {
        /** @var DB $db */
        $db = RegistrySingleton::getInstance()->get('db');
        $db->query(sprintf('UPDATE %s SET deleted_at=now() WHERE `order_id` = %u',
                           DB_PREFIX . self::TABLE_NAME,
                           $orderId));
    }

    public static function isOrderCreated(int $orderId)
    {
        $db    = RegistrySingleton::getInstance()->get('db');
        $query = $db->query("SELECT * FROM `" . DB_PREFIX . "cdek_order_meta` WHERE `order_id` = " . $orderId);
        if ($query->num_rows !== 0 && $query->row['created'] == 1) {
            return ['created' => true, 'row' => $query->row];
        }
        return ['created' => false];
    }

    public static function isOrderDeleted(int $orderId)
    {
        $db    = RegistrySingleton::getInstance()->get('db');
        $query = $db->query("SELECT * FROM `" . DB_PREFIX . "cdek_order_meta` WHERE `order_id` = " . $orderId);
        if ($query->num_rows !== 0 && $query->row['deleted'] == 1) {
            return ['deleted' => true, 'row' => $query->row];
        }
        return ['deleted' => false];
    }
}
