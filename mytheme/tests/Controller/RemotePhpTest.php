<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RemotePhpTest extends WebTestCase
{
    public static $order_data = [
        'phone' => '18888888888',
        'name' => '测试订单name',
        'address' => '测试订单address',
        'order' => [
            'customer_note' => '测试订单',
            'order_key' => '20230512123456',
            'date_created' => [
                'date' => '2023-05-12',
                'time' => '12:34:56',
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
            '_order_date' => '2023-05-12',
            '_order_time' => '12:34:56',
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
}
