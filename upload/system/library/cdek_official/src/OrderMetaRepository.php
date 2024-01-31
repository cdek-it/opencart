<?php

namespace CDEK;

use DB;

class OrderMetaRepository
{
    public static function create(): void
    {
        /** @var DB $db */
        $db          = RegistrySingleton::getInstance()->get('db');
        $prefix      = DB_PREFIX;
        $tableExists = $db->query("SHOW TABLES LIKE '{$prefix}cdek_order_meta'")->num_rows > 0;

        if (!$tableExists) {
            $createTableSQL = "CREATE TABLE `{$prefix}cdek_order_meta` (
                                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                                    `order_id` INT(11) NOT NULL,
                                    `cdek_number` VARCHAR(255) NOT NULL,
                                    `cdek_uuid` VARCHAR(255) NOT NULL,
                                    `name` VARCHAR(255) NOT NULL,
                                    `type` VARCHAR(255) NOT NULL,
                                    `payment_type` VARCHAR(255) NOT NULL,
                                    `to_location` VARCHAR(255) NOT NULL,
                                    `pvz_code` VARCHAR(255) NOT NULL,
                                    `created` INT(1) DEFAULT 0,
                                    `deleted` INT(1) DEFAULT 0,
                                    PRIMARY KEY (`id`),
                                    UNIQUE KEY `order_id_unique` (`order_id`),
                                    FOREIGN KEY (`order_id`) REFERENCES `{$prefix}order`(`order_id`)
                                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
            $db->query($createTableSQL);
        }

        $existingColumns = array_map(static fn($el) => $el['Field'],
            $db->query("SHOW COLUMNS FROM `{$prefix}cdek_order_meta`")->rows);

        $columnsToAdd = [
            'deleted' => 'deleted INT(1) DEFAULT 0',
        ];

        $columnsToAddSQL = array_diff(array_keys($columnsToAdd), $existingColumns);
        $columnsForAdd   = array_map(static fn($em) => $columnsToAdd[$em], $columnsToAddSQL);

        if (!empty($columnsToAddSQL)) {
            $alterTableSQL = "ALTER TABLE `{$prefix}cdek_order_meta` ADD " . implode(", ADD ", $columnsForAdd);
            $db->query($alterTableSQL);
        }
    }

    public static function insertOfficeCode(int $orderId, string $pvzCode): void
    {
        /** @var DB $db */
        $db     = RegistrySingleton::getInstance()->get('db');
        $prefix = DB_PREFIX;
        $db->query(sprintf("INSERT INTO %scdek_order_meta 
                                        (order_id, pvz_code) 
                                        VALUES (%u, '%s') 
                                        ON DUPLICATE KEY UPDATE
                                        pvz_code = VALUES(pvz_code)",
                           $prefix,
                           $orderId,
                           $db->escape($pvzCode)));
    }

    public static function getOrder(int $orderId): ?array
    {
        /** @var DB $db */
        $db     = RegistrySingleton::getInstance()->get('db');
        $prefix = DB_PREFIX;
        $query  = $db->query("SELECT * FROM `${prefix}cdek_order_meta` WHERE `order_id` = $orderId");

        return $query->num_rows === 0 ? null : $query->rows[0];
    }

    public static function insertOrderMeta(array $data, int $orderId): void
    {
        /** @var DB $db */
        $db = RegistrySingleton::getInstance()->get('db');
        if (!is_numeric($data['cdek_number'])) {
            $data['cdek_number'] = null;
        }

        $db->query(sprintf("INSERT INTO %scdek_order_meta 
                                        (order_id, cdek_number, cdek_uuid, name, type, payment_type, to_location, created) 
                                        VALUES (%u, '%s', '%s', '%s', '%s', '%s', '%s', 1) 
                                        ON DUPLICATE KEY UPDATE 
                                            cdek_number = VALUES(cdek_number), 
                                            cdek_uuid = VALUES(cdek_uuid), 
                                            name = VALUES(name), 
                                            type = VALUES(type), 
                                            payment_type = VALUES(payment_type),
                                            to_location = VALUES(to_location),
                                            created = VALUES(created),
                                            deleted = 0",
                           DB_PREFIX,
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
        $db->query("UPDATE `" . DB_PREFIX . "cdek_order_meta` SET created=0, deleted=1 WHERE `order_id` = $orderId");
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
