<?php

function updateAssets()
{
  $manifetsFile = __DIR__ . '/public/build/manifest.json';
  $entrypointsFile = __DIR__ . '/public/build/entrypoints.json';
  $runtimeFiel = glob(__DIR__ . '/public/build/runtime*.js');

  $baseName = '/' . basename(__DIR__) . '/';
  $replace = '/{{ themes }}/';
  if (file_exists($manifetsFile)) {
    $content = file_get_contents($manifetsFile);
    if (str_contains($content, $replace)) {
      file_put_contents($manifetsFile, str_replace($replace, $baseName, $content));
    }
  }

  if (file_exists($entrypointsFile)) {
    $content = file_get_contents($entrypointsFile);
    if (str_contains($content, $replace)) {
      file_put_contents($entrypointsFile, str_replace($replace, $baseName, $content));
    }
  }

  if (is_array($runtimeFiel)) {
    foreach ($runtimeFiel as $file) {
      $content = file_get_contents($file);
      if (str_contains($content, $replace)) {
        file_put_contents($file, str_replace($replace, $baseName, $content));
      }
    }
  }
}

function updateVersion($version)
{
  if (file_exists(__DIR__ . '/version.lock')) {
    $oldVersion = file_get_contents(__DIR__ . '/version.lock');

    if (version_compare($version, $oldVersion, 'eq')) {
      return;
    }
  }

  updateAssets();

  file_put_contents(__DIR__ . '/version.lock', $version);
}

function doUpgrade($version = null): void
{
  global $wpdb;

  require_once ABSPATH . 'wp-admin/includes/upgrade.php';

  $dataList = [
    "ALTER TABLE `{$wpdb->prefix}res_order` CHANGE `create_time` `create_time` DATETIME NOT NULL COMMENT '创建时间';",
    //     "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}res_menu_discount` (
    //         `id` bigint NOT NULL AUTO_INCREMENT,
    //         `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //         `description` text COLLATE utf8mb4_unicode_ci,
    //         `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
    //         `discount_percent` decimal(10,2) NOT NULL DEFAULT '0.00',
    //         `discount_type` tinyint(1) NOT NULL DEFAULT '0',
    //         `discount_start_time` datetime DEFAULT NULL,
    //         `discount_end_time` datetime DEFAULT NULL,
    //         `discount_menus` LONGTEXT DEFAULT NULL,
    //         `is_delete` tinyint(1) NOT NULL DEFAULT '0',
    //         `status` tinyint(1) NOT NULL DEFAULT '0',
    //         `discout_date` datetime DEFAULT NULL,
    //         `create_time` datetime DEFAULT NULL,
    //         `update_time` datetime DEFAULT NULL,
    //         PRIMARY KEY (`id`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    //     'ALTER TABLE `' . $wpdb->prefix . "res_order` ADD COLUMN `pay_discount` decimal(10,2) NULL DEFAULT '0.00' COMMENT '支付折扣' AFTER `order_status`,ADD COLUMN `pay_discount_type` TINYINT(1) NULL DEFAULT '0' COMMENT '支付折扣类型' AFTER `order_status`;",
    //     'UPDATE `' . $wpdb->prefix . "res_desk` SET `add_time = '2020-01-01 00:00:00' WHERE add_time = '0000-00-00 00:00:00'",
    //     'UPDATE `' . $wpdb->prefix . "res_order` SET `create_time` = '2020-01-01 00:00:00' WHERE `create_time` = '0000-00-00 00:00:00' or `create_time` = NULL",
    //     'UPDATE `' . $wpdb->prefix . "res_order` SET `pay_time` = '2020-01-01 00:00:00' WHERE `pay_time` = '0000-00-00 00:00:00' or `pay_time` = NULL",
    //     'UPDATE `' . $wpdb->prefix . "res_order` SET `update_time` = '2020-01-01 00:00:00' WHERE `update_time` = '0000-00-00 00:00:00' or `update_time` = NULL",
    //     'UPDATE `' . $wpdb->prefix . "res_order` SET `confirm_time` = '2020-01-01 00:00:00' WHERE `confirm_time` = '0000-00-00 00:00:00' or `confirm_time` = NULL",
    //     'ALTER TABLE `' . $wpdb->prefix . "res_order` CHANGE `create_time` `create_time` DATETIME NOT NULL COMMENT '创建时间', CHANGE `pay_time` `pay_time` DATETIME NULL COMMENT '支付时间', CHANGE `update_time` `update_time` DATETIME NOT NULL COMMENT '更新时间', CHANGE `confirm_time` `confirm_time` DATETIME NULL COMMENT '完成时间'",
    //     'ALTER TABLE `' . $wpdb->prefix . 'res_menu` CHANGE `create_time` `create_time` DATETIME NOT NULL',
    //     'ALTER TABLE `' . $wpdb->prefix . "res_menu` CHANGE `add_time` `add_time` DATETIME NOT NULL COMMENT '添加时间'",
    //     'ALTER TABLE `' . $wpdb->prefix . "res_desk` CHANGE `add_time` `add_time` DATETIME NOT NULL COMMENT '添加时间'",
    //     'ALTER TABLE `' . $wpdb->prefix . "res_order` ADD COLUMN `pay_type` TINYINT(2) NOT NULL DEFAULT '0' COMMENT '支付方式' AFTER `order_status`;",
    //     'ALTER TABLE `' . $wpdb->prefix . 'res_order` ADD INDEX `order_sn` (`order_sn`), ADD INDEX `desk_id` (`desk_id`);',
    //     'ALTER TABLE `' . $wpdb->prefix . 'res_order_detail` ADD INDEX `menu_id` (`menu_id`), ADD INDEX `oid` (`oid`);'
  ];

  foreach ($dataList as $sql) {
    $wpdb->query($sql);
    try {
      dbDelta($sql);
    } catch (\Exception $e) {
    }
  }

  if ($version) {
    updateVersion($version);
  }
}
