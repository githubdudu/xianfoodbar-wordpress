<?php

namespace App\Tests\Controller;

use Dbout\WpOrm\Orm\Database;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use wpdb;

class RemotePhpTest extends WebTestCase
{
    /**
     * Replace the $wpdb stub and reset the WP-ORM Database singleton so it
     * picks up the new stub on the next query.
     */
    private function setupWpDb(array $nextResults = [], mixed $nextRow = null): void
    {
        $mock = new wpdb();
        $mock->nextResults = $nextResults;
        $mock->nextRow = $nextRow;
        $GLOBALS['wpdb'] = $mock;

        $ref = new ReflectionClass(Database::class);
        $prop = $ref->getProperty('instance');
        // In newer PHP versions (8.1+), ReflectionProperty::setAccessible() is effectively unnecessary for
        // many reflection operations and has been deprecated in PHP 8.5.
        // Reflection can already access private/protected properties in most cases.
        $prop->setValue(null, null);
    }

  /**
   * @var array should be the same format as the docs/wooCommerceOrderSample.json
   */
    public static array $order_data = [
        'order_id' => 123456,
        'phone' => '18888888888',
        'name' => '测试订单name',
        'address' => '测试订单address',
        'order' => [
            'customer_note' => '测试订单',
            'order_key' => '20230512123456',
            'date_created' => [
                'date' => '2023-05-12 12:34:56.000000', // should be format "2025-12-04 19:18:46.000000",
                "timezone_type" => 3,
                "timezone" => "Pacific\/Auckland",
            ],
            'status' => 'completed',
        ],
        'total' => 100,
        'items' => [
            [
                'product_id' => '123456',
                'quantity' => 1,
                'subtotal' => 100,
                'meta_data' => [
                    [
                        'key' => 'Large',
                        'value' => '加大',
                    ],
                    [
                        'key' => 'Meat',
                        'value' => '加肉',
                    ],
                    [
                        'key' => 'Vegs',
                        'value' => '加菜',
                    ],
                ],
            ],
        ],
        'metas' => [
            '_order_date' => '04.12.2025',
            '_order_time' => '730',
            '_before_checkout_billing_form_pick_up_or_delivery' => 'delivery',
            'is_vat_exempt' => 'yes',
        ],
        'status' => 'completed',
    ];

    /**
     * Test when no id is passed. Should return 404.
     */
    public function testNoId(): void
    {
        $client = static::createClient();
        $crawler = $client->request('POST', '/api/remote/getdata');

        $this->assertResponseStatusCodeSame(404, 'Response should be 404 when no id is passed');
    }

    /**
     * Test when order data is empty. Should return 404.
     */
    public function testEmptyOrderData(): void
    {
        $client = static::createClient();
        $order_id = 123456;
        $crawler = $client->request('POST', '/api/remote/getdata/' . $order_id, [], [], ['CONTENT_TYPE' => 'application/json'], '{}');

        $this->assertResponseStatusCodeSame(404, 'Response should be 404 when order data is empty');
    }

    /**
     * Test when stale order that is older than 12 hours. Should return 200.
     */
    public function testStaleOrder(): void
    {
        $client = static::createClient();
        define('ORDER_ID', 123456);
        define('EMPTY_DATA', '');
        $client->request('POST', '/api/remote/getdata/' . ORDER_ID, self::$order_data);

        $this->assertResponseStatusCodeSame(200, 'Response should be 200 for a stale order (older than 12 hours)');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(EMPTY_DATA, $data['message'], 'Response should be empty for a stale order (older than 12 hours)');
    }

    /**
     * Test when an existing order already has status 2 (completed). Should return 200 with 'completed'.
     */
    public function testCompletedExistingOrder(): void
    {
        $this->setupWpDb(
            nextResults: [(object)['order_status' => 2, 'takeway_order' => 'orderdata_123456']]
        );

        $client = static::createClient();
        $orderData = self::$order_data;
        $orderData['order']['date_created']['date'] = date('Y-m-d');

        $client->request('POST', '/api/remote/getdata/123456', $orderData);

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('completed', $data['message']);
    }
}
