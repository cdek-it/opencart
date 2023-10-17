<?php

namespace CDEK;

class CdekOrderMetaRepository
{
    public static function create($db, $prefix)
    {
        $tableExists = $db->query("SHOW TABLES LIKE '{$prefix}cdek_order_meta'")->num_rows > 0;

        if (!$tableExists) {
            $createTableSQL = "
            CREATE TABLE `{$prefix}cdek_order_meta` (
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
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ";
            $db->query($createTableSQL);
        }

        $existingColumns = array_map(fn($el) => $el['Field'], $db->query("SHOW COLUMNS FROM `" . $prefix . "cdek_order_meta`")->rows);

        $columnsToAdd = [
            'deleted' => 'deleted INT(1) DEFAULT 0',
        ];

        $columnsToAddSQL = array_diff(array_keys($columnsToAdd), $existingColumns);
        $columnsForAdd = array_map(fn($em) => $columnsToAdd[$em], $columnsToAddSQL);

        if (!empty($columnsToAddSQL)) {
            $alterTableSQL = "ALTER TABLE `{$prefix}cdek_order_meta` ADD " . implode(", ADD ", $columnsForAdd);
            $db->query($alterTableSQL);
        }
    }

    public static function insertPvzCode($db, $prefix, $orderId, $pvzCode)
    {
        $db->query(
            "INSERT INTO {$prefix}cdek_order_meta SET order_id = " . $orderId
            . ", pvz_code = '" . $db->escape($pvzCode) . "'"
            . " ON DUPLICATE KEY UPDATE "
            . "pvz_code = VALUES(pvz_code)"
        );
    }

    public static function getOrder($db, $orderId)
    {
        return $db->query("SELECT * FROM `" . DB_PREFIX . "cdek_order_meta` WHERE `order_id` = " . $orderId);
    }

    public static function insertOrderMeta($db, array $data, int $orderId)
    {
        if (!is_numeric($data['cdek_number'])) {
            $data['cdek_number'] = null;
        }

        $db->query(
            "INSERT INTO " . DB_PREFIX . "cdek_order_meta SET order_id = " . $orderId
            . ", cdek_number = '" . $db->escape($data['cdek_number']) . "'"
            . ", cdek_uuid = '" . $db->escape($data['cdek_uuid']) . "'"
            . ", name = '" . $db->escape($data['name']) . "'"
            . ", type = '" . $db->escape($data['type']) . "'"
            . ", payment_type = '" . $db->escape($data['payment_type']) . "'"
            . ", to_location = '" . $db->escape($data['to_location']) . "'"
            . ", created = 1"
            . " ON DUPLICATE KEY UPDATE "
            . "cdek_number = VALUES(cdek_number), "
            . "cdek_uuid = VALUES(cdek_uuid), "
            . "name = VALUES(name), "
            . "type = VALUES(type), "
            . "payment_type = VALUES(payment_type), "
            . "to_location = VALUES(to_location), "
            . "created = VALUES(created)"
        );

        $db->query("UPDATE `" . DB_PREFIX . "cdek_order_meta` SET deleted=0 WHERE `order_id` = " . $orderId);
    }

    public static function deleteOrder($db, int $orderId)
    {
        $db->query("UPDATE `" . DB_PREFIX . "cdek_order_meta` SET created=0, deleted=1 WHERE `order_id` = " . $orderId);
    }

    public static function isOrderCreated($db, int $orderId)
    {
        $query = $db->query("SELECT * FROM `" . DB_PREFIX . "cdek_order_meta` WHERE `order_id` = " . $orderId);
        if ($query->num_rows !== 0 && $query->row['created'] == 1) {
            return ['created' => true, 'row' => $query->row];
        }
        return ['created' => false];
    }

    public static function isOrderDeleted($db, int $orderId)
    {
        $query = $db->query("SELECT * FROM `" . DB_PREFIX . "cdek_order_meta` WHERE `order_id` = " . $orderId);
        if ($query->num_rows !== 0 && $query->row['deleted'] == 1) {
            return ['deleted' => true, 'row' => $query->row];
        }
        return ['deleted' => false];
    }
}