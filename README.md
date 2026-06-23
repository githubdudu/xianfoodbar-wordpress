# Dev records

Setup a local dev environment for Xianfoodbar
1. Create a Docker Compose setup that runs WordPress + MySQL locally, with the theme mounted in the correct path, developer-friendly env, and writable directories.
2. Frontend assets pre-built in `public/build/` and `public/umi/` — no Node.js needed
3. Setup WordPress admin panel with a restaurant admin account
4. Setup 固定链接设置 in WordPress admin panel. Choose 文章名 and save it.
5. Navigate to the /adminpanel/login page and login with the restaurant admin account

# Online orders

To test the Remote.php webhook flow without standing up WooCommerce, fire the webhook manually.

## Basic order (no metadata)

```bash
curl -X POST http://localhost:8080/api/remote/getdata/123 \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": 123,
    "phone": "021000000",
    "name": "Test User",
    "address": "123 Test St",
    "total": "25.00",
    "status": "processing",
    "order": {
      "customer_note": "",
      "order_key": "wc_test",
      "date_created": {"date": "'"$(date '+%Y-%m-%d %H:%M:%S')"'"}
    },
    "items": [{"product_id": 37, "quantity": 2, "subtotal": "25.00", "meta_data": []}],
    "metas": {}
  }'
```

## Delivery order with extras and pickup time

```bash
curl -s -X POST http://localhost:8080/api/remote/getdata/789 \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "021000000",
    "name": "Test User",
    "address": "123 Test St",
    "total": "25.00",
    "status": "processing",
    "order": {
      "customer_note": "No spicy please",
      "order_key": "wc_test_789",
      "date_created": {"date": "'"$(date '+%Y-%m-%d %H:%M:%S')"'"}
    },
    "items": [
      {
        "product_id": 37,
        "quantity": 1,
        "subtotal": "25.00",
        "name": "37.腊汁肉干拌面",
        "meta_data": [
          {
            "id": 38,
            "key": "38.加大Extra Large (+&#36;1.50)",
            "value": "L"
          },
          {
            "id": 132,
            "key": "加菜，蛋，肉，面 Extras (+&#36;5.50)",
            "value": "132.加肉.../份Extra Meat"
          },
          {
            "id": 370,
            "key": "370.腊汁肉机切面",
            "value": "腊汁肉机切面"
          }
        ]
      },
      {
        "product_id": 37,
        "quantity": 1,
        "subtotal": "25.00",
        "name": "37.腊汁肉干拌面",
        "meta_data": [
          {
            "id": 38,
            "key": "38.加大Extra Large (+&#36;1.50)",
            "value": "L"
          }
        ]
      }
    ],
    "metas": {
      "is_vat_exempt": "no",
      "_before_checkout_billing_form_pick_up_or_delivery": "delivery",
      "_order_date": "04.05.26",
      "_order_estimated_delivery_time": "18.30"
    }
  }'
```

### Latest Orders

```bash
curl -s -X POST http://localhost:8080/api/remote/getdata/163 \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": 163,
    "phone": "021000000",
    "name": "Test User",
    "address": "123 Test St",
    "subtotal": 81.5,
    "total": "81.50",
    "status": "checkout-draft",
    "order": {
      "customer_note": "No spicy please",
      "order_key": "wc_test_789",
      "date_created": {
        "date": "'"$(date '+%Y-%m-%d %H:%M:%S')"'",
        "timezone": "Pacific\/Auckland"
        }
    },
    "items": [
        {
            "id": 32,
            "order_id": 162,
            "name": "37.腊汁肉干拌手扯面",
            "product_id": 31,
            "variation_id": 0,
            "quantity": 3,
            "tax_class": "",
            "subtotal": "49.5",
            "subtotal_tax": "0",
            "total": "49.5",
            "total_tax": "0",
            "taxes": {
                "total": [],
                "subtotal": []
            },
            "meta_data": [
                {
                    "id": 321,
                    "key": "38.加大Extra Large (+$1.50)",
                    "value": "L"
                },
                {
                    "id": 322,
                    "key": "_exoptions",
                    "value": [
                        {
                            "name": "38.加大Extra Large",
                            "value": "L",
                            "type_of_price": "",
                            "price": 1.5,
                            "_type": ""
                        }
                    ]
                }
            ]
        },
        {
            "id": 33,
            "order_id": 162,
            "name": "37.腊汁肉干拌手扯面",
            "product_id": 31,
            "variation_id": 0,
            "quantity": 1,
            "tax_class": "",
            "subtotal": "32",
            "subtotal_tax": "0",
            "total": "32",
            "total_tax": "0",
            "taxes": {
                "total": [],
                "subtotal": []
            },
            "meta_data": [
                {
                    "id": 332,
                    "key": "38.加大Extra Large (+$1.50)",
                    "value": "L"
                },
                {
                    "id": 333,
                    "key": "加菜，蛋，肉，面 Extras (+$2.50)",
                    "value": "130.加菜...\/份Extra Vegs"
                },
                {
                    "id": 334,
                    "key": "加菜，蛋，肉，面 Extras (+$3.00)",
                    "value": "131.加蛋...\/个Extra Egg"
                },
                {
                    "id": 335,
                    "key": "加菜，蛋，肉，面 Extras (+$5.50)",
                    "value": "132.加肉...\/份Extra Meat"
                },
                {
                    "id": 336,
                    "key": "加菜，蛋，肉，面 Extras (+$2.00)",
                    "value": "133.加面...Extra Noodles"
                },
                {
                    "id": 337,
                    "key": "加菜，蛋，肉，面 Extras (+$2.50)",
                    "value": "134.加拉条...Extra Stretched Noodles"
                },
                {
                    "id": 338,
                    "key": "_exoptions",
                    "value": [
                        {
                            "name": "38.加大Extra Large",
                            "value": "L",
                            "type_of_price": "",
                            "price": 1.5,
                            "_type": ""
                        },
                        {
                            "name": "加菜，蛋，肉，面 Extras",
                            "value": "130.加菜...\/份Extra Vegs",
                            "type_of_price": "",
                            "price": 2.5,
                            "_type": ""
                        },
                        {
                            "name": "加菜，蛋，肉，面 Extras",
                            "value": "131.加蛋...\/个Extra Egg",
                            "type_of_price": "",
                            "price": 3,
                            "_type": ""
                        },
                        {
                            "name": "加菜，蛋，肉，面 Extras",
                            "value": "132.加肉...\/份Extra Meat",
                            "type_of_price": "",
                            "price": 5.5,
                            "_type": ""
                        },
                        {
                            "name": "加菜，蛋，肉，面 Extras",
                            "value": "133.加面...Extra Noodles",
                            "type_of_price": "",
                            "price": 2,
                            "_type": ""
                        },
                        {
                            "name": "加菜，蛋，肉，面 Extras",
                            "value": "134.加拉条...Extra Stretched Noodles",
                            "type_of_price": "",
                            "price": 2.5,
                            "_type": ""
                        }
                    ]
                }
            ]
        }
    ],
    "metas": {
      "is_vat_exempt": "no",
      "_before_checkout_billing_form_pick_up_or_delivery": "delivery",
      "_order_date": "04.05.26",
      "_order_estimated_delivery_time": "18.30"
    }
  }'
```