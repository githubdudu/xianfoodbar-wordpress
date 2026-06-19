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
    "items": [{"product_id": 1, "quantity": 2, "subtotal": "25.00", "meta_data": []}],
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
        "product_id": 1,
        "quantity": 2,
        "subtotal": "25.00",
        "meta_data": [
          {"key": "Meat", "value": "Extra&#038;"},
          {"key": "Vegs", "value": "No vegetables"}
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
