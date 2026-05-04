<?php

namespace App\Controller\Admin;

use App\Core\Controller\Wordpress;
use App\Model\Desk;
use App\Model\Order;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;
use NumberFormatter;

class AdminConfig extends Wordpress
{
  #[Route('/api/admin/config', name: 'config_admin')]
  public function config(): \Symfony\Component\HttpFoundation\Response
  {
    if (!$this->isLogin()) {
      return $this->sendJson('没有权限访问', 403);
    }

    // set UTC +8
    $now = new DateTime();
    $now->setTime(0, 0, 0);
    $yesterday = new DateTime('-1 day');
    $yesterday->setTime(0, 0, 0);
    $timeList = date_range(new DateTime('-4 day'), new DateTime('+3 day'));

    $last = count($timeList) - 1;
    $columnList = [];

    $deskLists = Desk::all();
    $allTakeWayDesk = [];
    foreach ($deskLists as $desk) {
      if ($desk->is_takeway == 1) {
        $allTakeWayDesk[] = $desk;
      }
    }

    $configData = [
      'showInfoBar' => true,
      'inforBarTitle' => '',
      'infoData' => [],
      'useSudoku' => true,
      'sudokuList' => $deskLists,
      'columnConfig' => [
        // data: [],
      ],
      'columns' => [],
    ];

    $infoDate = [];
    $allOrderQuery = Order::where('confirm_time', '>=', $timeList[0])
      ->where('order_status', 2)
      ->where('is_delete', 0)
      ->where('is_cancel', 0)
      ->get();

    $allOrder = [];
    $todayTakewayOrder = [];
    $tpdayPayTypeOrder = [];
    $deskOrder = [];
    $takewayOrder = [];
    foreach ($allOrderQuery as $order) {
      $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $order->confirm_time);
      $today = $dateTime->format('Y-m-d');

      $nowDate = $now->format('Y-m-d');
      if ($today === $nowDate) {
        if (isset($todayTakewayOrder[$order->desk_id])) {
          $todayTakewayOrder[$order->desk_id]['count']++;
          $todayTakewayOrder[$order->desk_id]['price'] += $order->pay_price;
        } else {
          $todayTakewayOrder[$order->desk_id] = [
            'count' => 1,
            'price' => $order->pay_price,
          ];
        }

        if (isset($tpdayPayTypeOrder[$order->pay_type])) {
          $tpdayPayTypeOrder[$order->pay_type]['count']++;
          $tpdayPayTypeOrder[$order->pay_type]['price'] += $order->pay_price;
        } else {
          $tpdayPayTypeOrder[$order->pay_type] = [
            'count' => 1,
            'price' => $order->pay_price,
          ];
        }
      }
      if (isset($allOrder[$today])) {
        $allOrder[$today]['price'] += $order->pay_price;
        $allOrder[$today]['count']++;
      } else {
        $allOrder[$today] = [
          'price' => $order->pay_price,
          'count' => 1,
        ];
      }

      if ($order->is_takeway == 1) {
        if (isset($takewayOrder[$today])) {
          $takewayOrder[$today]['count']++;
          $takewayOrder[$today]['price'] += $order->pay_price;
        } else {
          $takewayOrder[$today] = [
            'count' => 1,
            'price' => $order->pay_price,
          ];
        }
      } else {
        if (isset($deskOrder[$today])) {
          $deskOrder[$today]['count']++;
          $deskOrder[$today]['price'] += $order->pay_price;
        } else {
          $deskOrder[$today] = [
            'count' => 1,
            'price' => $order->pay_price,
          ];
        }
      }
    }

    $infoDate[] = [
      'title' => '今日订单数',
      'value' => $allOrder[$now->format('Y-m-d')]['count'] ?? 0,
      'suffix' => '单',
    ];

    $infoDate[] = [
      'title' => '昨日订单',
      'value' => $allOrder[$yesterday->format('Y-m-d')]['count'] ?? 0,
      'suffix' => '单',
    ];

    $infoDate[] = [
      'title' => '今日销售额',
      'value' => number_format($allOrder[$now->format('Y-m-d')]['price'] ?? 0, 2, '.', ''),
      'precision' => '0.01',
      'suffix' => '元',
    ];

    $infoDate[] = [
      'title' => '昨日销售额',
      'value' => number_format($allOrder[$yesterday->format('Y-m-d')]['price'] ?? 0, 2, '.', ''),
      'suffix' => '元',
      'precision' => '0.01',
    ];

    $configData['infoData'] = $infoDate;

    $DayInfo = [
      'list' => [],
    ];

    $sevenDay = [
      'title' => '七日销售统计',
      'type' => 'column',
      'config' => [
        'isGroup' => true,
        'xField' => 'date',
        'yField' => 'money',
        'seriesField' => 'type',
        'label' => [
          'position' => 'middle',
        ],
        'meta' => [
          'date' => [
            'alise' => '日期',
          ],
          'money' => [
            'alise' => '销量',
          ],
        ],
        'data' => [],
      ],
    ];

    /**
     * @var DateTime $time
     */
    foreach ($timeList as $key => $time) {
      if ($key == $last) {
        break;
      }

      // $money        = $orderModel->getMoneyFormDay($time, $timeList[$key + 1]);
      // $columnList[] = [
      //     // 'key'   => 'date_' . $time . '_d',
      //     'date'  => date('m-d', $time),
      //     'money' => floatval($money ?: '0.00'),
      //     'type'  => '总数',
      // ];
      $newTime = clone $time;
      //$newTime->modify('+' . $time->getOffset() . ' seconds');
      $columnList[] = [
        // 'key'   => 'date_' . $time . '_d',
        'date' => $newTime->format('m-d'),
        'money' =>  round($deskOrder[$time->format('Y-m-d')]['price'] ?? 0, 2),
        'type' => '餐桌',
      ];
      $columnList[] = [
        // 'key'   => 'date_' . $time,
        'date' => $newTime->format('m-d'),
        'money' => round($takewayOrder[$time->format('Y-m-d')]['price'] ?? 0, 2),
        'type' => '外卖',
      ];
    }

    $sevenDay['config']['data'] = $columnList;
    $DayInfo['list'][] = $sevenDay;

    $pie = [
      'title' => '当日外卖销售额占比',
      'type' => 'pie',
      'config' => [
        'angleField' => 'money',
        'colorField' => 'type',
        'radius' => 0.75,
        'label' => [
          'type' => 'spider',
          'labelHeight' => 28,
          'content' => "{name}\n{value}",
        ],
        'interactions' => [
          [
            'type' => 'element-selected',
          ],
          [
            'type' => 'element-active',
          ],
        ],
        'data' => [],
      ],
    ];

    foreach ($allTakeWayDesk as $takewayDesk) {
      $money = isset($todayTakewayOrder[$takewayDesk->id]) ? $todayTakewayOrder[$takewayDesk->id]['price'] : 0;

      if ($money > 0) {
        $pie['config']['data'][] = [
          'money' => $this->parseMoney($money),
          'type' => $takewayDesk->getDeskName(),
        ];
      }
    }

    $pie2 = [
      'title' => '当日支付方式销售额占比',
      'type' => 'pie',
      'config' => [
        'angleField' => 'money',
        'colorField' => 'type',
        'radius' => 0.75,
        'label' => [
          'type' => 'spider',
          'labelHeight' => 28,
          'content' => "{name}\n{value}",
        ],
        'interactions' => [
          [
            'type' => 'element-selected',
          ],
          [
            'type' => 'element-active',
          ],
        ],
        'data' => [],
      ],
    ];

    $payType = getPayType();

    foreach ($payType as $payId => $payName) {
      $money = isset($tpdayPayTypeOrder[$payId]) ? $tpdayPayTypeOrder[$payId]['price'] : 0;
      if ($money > 0) {
        $pie2['config']['data'][] = [
          'money' => $this->parseMoney($money),
          'type' => $payName,
        ];
      }
    }

    // $deskMoney = $orderModel->getIsDeskMoneyFormDay($now, $tomorrow);
    // $takewayMoeny = $orderModel->getIsTakeWayMoneyFormDay($now, $tomorrow);

    // $pie['config']['data'] = [
    //     [
    //         'money' => $this->parseMoney($deskMoney),
    //         'type'  => '外卖',
    //     ],
    //     [
    //         'money' => $this->parseMoney($takewayMoeny),
    //         'type'  => '餐桌',
    //     ],
    // ];

    $configData['columns'] = [
      [
        'list' => [
          $pie,
          $pie2
        ]
      ],
      $DayInfo,
    ];

    $this->addJsonData('config', $configData);
    return $this->sendJson();
  }

  #[Route('/adminpanel/assets/update', 'update-assets')]
  public function updateAssets()
  {
    updateAssets();
    return $this->redirect('/adminpanel');
  }

  #[Route('/adminpanel/sql/update', 'update-sql')]
  public function updateSql()
  {
    doUpgrade();
    return $this->redirect('/adminpanel');
  }

  #[Route('/api/admin/get/admin/config', name: 'api_get_admin_config')]
  public function adminGetConfig()
  {
    if (!$this->isLogin()) {
      return $this->sendJson('没有权限访问', 403);
    }

    $configData = [];

    $userInfo = $this->getWpUser($this->nowUserId());
    if ($userInfo) {
      $configData['avatar'] = $userInfo['user_head'] ?? null;
    }

    $configData['menus'] = [
      [
        'menu_name' => '系统设置',
        'icon' => 'SettingOutlined',
        'url' => $this->generateFormUrl('adminSetting', 'list'),
      ],
    ];

    $this->addJsonData('config', $configData);
    return $this->sendJson();
  }

  private function parseMoney($money)
  {
    if ($money == 0) {
      return intval($money);
    }

    $formatter = new NumberFormatter('en', NumberFormatter::DECIMAL);
    $formatter->setPattern('##0.00');

    return floatval($formatter->format($money, NumberFormatter::TYPE_DOUBLE));
  }
}
