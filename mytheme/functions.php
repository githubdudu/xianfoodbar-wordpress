<?php

date_default_timezone_set('NZ');
// ini_set('date.timezone', 'Etc/GMT+12');
$version = '2.4.9';
include __DIR__ . '/upgrade.php';

// 设置支付方式
function getPayType()
{
  // 必须是数字 => '支付方式'
  return [
    1 => '刷卡',
    2 => '现金',
    3 => '微信支付宝',
    4 => '转账',
    5 => '其他',
  ];
}

function getSelectPayType()
{
  $dataArray = [];
  $data = getPayType();
  if (!isset($data[0])) {
    $data[0] = '未选择';
  }

  ksort($data);
  foreach ($data as $id => $label) {
    $dataArray[] = [
      'label' => $label,
      'value' => $id,
    ];
  }

  return $dataArray;
}

function getDefaultPayType()
{
  // 修改下方数字达到默认选择的方式
  return 1;
}

if (!file_exists(__DIR__ . '/version.lock')) {
  // manifets and entrypoints
  updateVersion($version);
} else {
  $oldVersion = file_get_contents(__DIR__ . '/version.lock');

  if (version_compare($version, $oldVersion, '>')) {
    doUpgrade($version);
  }
}

add_action('after_switch_theme', 'createLevelTable');

function createLevelTable()
{
  global $wpdb;

  $installer_data = [
    'res_desk' => 'CREATE TABLE IF NOT EXISTS `' . $wpdb->prefix . "res_desk` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `desk_name` varchar(100) NOT NULL COMMENT '桌号',
            `desk_subname` varchar(255) NOT NULL,
            `desk_status` tinyint(1) NOT NULL COMMENT '使用状态',
            `add_time` DATETIME NOT NULL COMMENT '添加时间',
            `use_status` tinyint(1) NOT NULL COMMENT '使用状态',
            `menu_guid` varchar(20) NOT NULL,
            `is_takeway` tinyint(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    'res_menu' => 'CREATE TABLE IF NOT EXISTS `' . $wpdb->prefix . "res_menu` (
            `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '菜品ID',
            `menu_name` varchar(100) NOT NULL  COMMENT '菜品名',
            `menu_subname` varchar(255) NOT NULL,
            `menu_note` text NOT NULL,
            `menu_num` varchar(100) NOT NULL,
            `menu_price` decimal(10,2) NOT NULL COMMENT '菜品价格',
            `menu_count` int(11) NOT NULL COMMENT '菜品数量',
            `menu_category` int(11) NOT NULL,
            `menu_sales` int(11) NOT NULL,
            `add_time` DATETIME NOT NULL COMMENT '添加时间',
            `status` tinyint(1) NOT NULL COMMENT '状态',
            `out_site_id` bigint(20) NOT NULL DEFAULT '0',
            `is_attr` tinyint(1) NOT NULL DEFAULT '0',
            `is_delete` tinyint(1) NOT NULL COMMENT '是否删除',
            `is_hot` tinyint(1) NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    'res_menu_category' => 'CREATE TABLE `' . $wpdb->prefix . 'res_menu_category` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `category_name` varchar(30) NOT NULL,
            `create_time` DATETIME NOT NULL,
            `is_delete` tinyint(1) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;',
    'res_order' => 'CREATE TABLE IF NOT EXISTS `' . $wpdb->prefix . "res_order` (
            `oid` int(11) NOT NULL AUTO_INCREMENT,
            `order_sn` varchar(100) NOT NULL COMMENT '订单编号',
            `create_time` varchar(20) NOT NULL COMMENT '创建时间',
            `pay_time` varchar(20) NOT NULL COMMENT '支付时间',
            `update_time` varchar(20) NOT NULL COMMENT '更新时间',
            `confirm_time` varchar(20) NOT NULL COMMENT '完成时间',
            `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
            `is_cancel` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否取消',
            `order_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单状态，0 已下单 1 已支付 2 已完成',
            `desk_id` int(11) NOT NULL COMMENT '桌号',
            `is_pin` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否为拼座',
            `pin_num` tinyint(1) DEFAULT '0',
            `pay_price` decimal(10,2) NOT NULL COMMENT '支付价格',
            `is_takeway` int(11) NOT NULL DEFAULT '0' COMMENT '是否为外卖 > 0则为外卖',
            `pay_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '支付方式',
            `user_id` int(11) NOT NULL COMMENT '负责的服务员ID',
            `takeway_order` varchar(20) NOT NULL COMMENT '外卖订单编号',
            `address` varchar(500) NOT NULL COMMENT '外卖的送餐地址',
            `realname` varchar(200) NOT NULL,
            `phone` varchar(30) NOT NULL,
            `note` text NOT NULL,
            `is_checked` tinyint(1) NOT NULL,
            `is_vat_exempt` tinyint(1) NOT NULL,
            `delivery_order_date` varchar(100) NOT NULL,
            `is_delivery` tinyint(1) NOT NULL,
            `is_read` tinyint(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (`oid`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    'res_order_detail' => 'CREATE TABLE IF NOT EXISTS `' . $wpdb->prefix . "res_order_detail` (
            `odid` int(11) NOT NULL AUTO_INCREMENT,
            `oid` int(11) NOT NULL COMMENT '订单id',
            `menu_id` int(11) NOT NULL COMMENT '菜品id',
            `total` int(11) NOT NULL COMMENT '数量',
            `price` decimal(10,2) NOT NULL COMMENT '单价',
            `note` text NOT NULL,
            `add_time` varchar(20) NOT NULL COMMENT '添加时间',
            `menu_name` varchar(100) NOT NULL COMMENT '菜品名',
            `total_price` decimal(10,2) NOT NULL COMMENT '总价',
            `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
            `add_count` tinyint(4) NOT NULL DEFAULT '0' COMMENT '上了几份',
            PRIMARY KEY (`odid`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    'res_menu_discount' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}res_menu_discount` (
            `id` bigint NOT NULL AUTO_INCREMENT,
            `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `description` text COLLATE utf8mb4_unicode_ci,
            `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
            `discount_percent` decimal(10,2) NOT NULL DEFAULT '0.00',
            `discount_type` tinyint(1) NOT NULL DEFAULT '0',
            `discount_start_time` datetime DEFAULT NULL,
            `discount_end_time` datetime DEFAULT NULL,
            `discount_menus` LONGTEXT DEFAULT NULL,
            `is_delete` tinyint(1) NOT NULL DEFAULT '0',
            `status` tinyint(1) NOT NULL DEFAULT '0',
            `discout_date` datetime DEFAULT NULL,
            `create_time` datetime DEFAULT NULL,
            `update_time` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
  ];
  require_once ABSPATH . 'wp-admin/includes/upgrade.php';

  foreach ($installer_data as $key => $sql) {
    // $table_name = $wpdb->prefix . $key;
    dbDelta($sql);
  }
}

/**
 * @param DateTimeInterface $start
 * @param DateTimeInterface $last
 * @param $step
 * @param $format
 * @return DateTimeInterface[]
 */
function date_range(DateTimeInterface $start, DateTimeInterface $last, int $step = 1): array
{
  $dates = [];
  $current = $start;
  while ($current <= $last) {
    $dates[] = clone $current;
    $current = date_push($current, "P{$step}D");
  }
  return $dates;
}

function date_push(DateTimeInterface $date, string $duration = 'P1D'): DateTimeInterface
{
  try {
    return $date->add(new \DateInterval($duration));
  } catch (Exception $e) {
    return $date;
  }
}

function date_pull(DateTimeInterface $date, string $duration = 'P1D'): DateTimeInterface
{
  try {
    return $date->sub(new \DateInterval($duration));
  } catch (Exception $e) {
    return $date;
  }
}

show_admin_bar(false);

// 添加style.css
add_action('wp_enqueue_scripts', function () {
  wp_enqueue_style('style', get_stylesheet_uri());
});
