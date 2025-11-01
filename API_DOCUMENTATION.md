# PIMS API Documentation

Base URL: `http://localhost:8000/api`

All protected endpoints require `Authorization: Bearer {token}` header.

## Authentication Endpoints

### Google OAuth
- `GET /auth/google` - Redirect to Google OAuth
- `GET /auth/google/callback` - Handle Google OAuth callback
- `POST /auth/complete-profile` - Complete user profile with company
- `POST /auth/find-companies` - Find companies by email
- `POST /auth/link-company` - Link user to existing company
- `POST /auth/logout` - Logout user
- `GET /user` - Get authenticated user details

---

## Dashboard Endpoints

### GET `/dashboard/stats`
Get dashboard statistics including products, orders, sales, customers count and changes.

**Response:**
```json
{
  "stats": {
    "total_products": 156,
    "total_orders": 43,
    "total_sales": 89,
    "low_stock_items": 12,
    "monthly_sales": 45000.00,
    "total_customers": 28
  },
  "changes": {
    "products": "+12%",
    "orders": "+8%",
    "sales": "+15%",
    "low_stock": "-3%"
  }
}
```

### GET `/dashboard/recent-activities`
Get last 10 recent activities across orders, sales, products, and alerts.

### GET `/dashboard/sales-overview`
Get 30-day sales chart data.

### GET `/dashboard/top-products`
Get top 10 selling products (last 30 days).

### GET `/dashboard/inventory-summary`
Get inventory overview stats.

---

## Raw Materials Endpoints

### GET `/raw-materials`
Get all raw materials for the company.

**Response:**
```json
{
  "materials": [
    {
      "id": 1,
      "material": "Steel",
      "description": "High grade steel",
      "base_unit": "kg",
      "minimum_stock": 100.000,
      "units": [...]
    }
  ]
}
```

### POST `/raw-materials`
Create a new raw material.

**Body:**
```json
{
  "material": "Steel",
  "description": "High grade steel",
  "base_unit": "kg",
  "minimum_stock": 100
}
```

### PUT `/raw-materials/{id}`
Update a raw material.

### DELETE `/raw-materials/{id}`
Delete a raw material.

### POST `/raw-materials/batch`
Batch create materials.

**Body:**
```json
{
  "materials": [
    {
      "name": "Steel",
      "base_unit": "kg"
    },
    {
      "name": "Aluminum",
      "base_unit": "kg"
    }
  ]
}
```

### GET `/raw-materials/{materialId}/units`
Get all units for a material.

### POST `/raw-materials/{materialId}/units`
Add a unit to a material.

**Body:**
```json
{
  "unit_name": "ton",
  "conversion_factor": 1000
}
```

### DELETE `/raw-materials/{materialId}/units/{unitId}`
Delete a unit.

---

## Customers Endpoints

### GET `/customers`
Get all customers.

### POST `/customers`
Create a new customer.

**Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "1234567890",
  "address": "123 Main St"
}
```

### GET `/customers/{id}`
Get a single customer.

### PUT `/customers/{id}`
Update a customer.

### DELETE `/customers/{id}`
Delete a customer.

### POST `/customers/batch`
Batch create customers.

**Body:**
```json
{
  "customers": [
    {
      "name": "Customer 1",
      "email": "cust1@example.com"
    },
    {
      "name": "Customer 2",
      "phone": "9876543210"
    }
  ]
}
```

---

## Raw Material Purchases Endpoints

### GET `/rm-purchases`
Get all purchases.

**Response:**
```json
{
  "purchases": [
    {
      "id": 1,
      "material": {...},
      "unit": {...},
      "supplier_name": "ABC Supplier",
      "purchase_date": "2025-11-01",
      "qty": 500.000,
      "rate": 50.00,
      "total_amount": 25000.00
    }
  ]
}
```

### POST `/rm-purchases`
Create a new purchase.

**Body:**
```json
{
  "material_id": 1,
  "supplier_name": "ABC Supplier",
  "purchase_date": "2025-11-01",
  "qty": 500,
  "unit_id": 1,
  "rate": 50,
  "batch_number": "BATCH001",
  "expiry_date": "2026-11-01"
}
```

### PUT `/rm-purchases/{id}`
Update a purchase.

### DELETE `/rm-purchases/{id}`
Delete a purchase.

### POST `/rm-purchases/batch`
Batch create purchases.

**Body:**
```json
{
  "purchases": [
    {
      "material_id": 1,
      "purchase_date": "2025-11-01",
      "qty": 100,
      "unit_id": 1,
      "rate": 50
    }
  ]
}
```

---

## Finished Products Endpoints

### GET `/products`
Get all products.

### POST `/products`
Create a new product.

**Body:**
```json
{
  "product_name": "Widget A",
  "description": "High quality widget",
  "base_unit": "pieces",
  "selling_price": 100,
  "minimum_stock": 50
}
```

### GET `/products/{id}`
Get a single product.

### PUT `/products/{id}`
Update a product.

### DELETE `/products/{id}`
Delete a product.

### POST `/products/{id}/toggle-active`
Toggle product active status.

---

## Orders Endpoints

### GET `/orders`
Get all orders.

**Response:**
```json
{
  "orders": [
    {
      "id": 1,
      "customer": {...},
      "product": {...},
      "qty": 10.000,
      "unit_price": 100.00,
      "total_amount": 1000.00,
      "order_date": "2025-11-01",
      "status": "pending"
    }
  ]
}
```

### POST `/orders`
Create a new order.

**Body:**
```json
{
  "customer_id": 1,
  "product_id": 1,
  "qty": 10,
  "unit_price": 100,
  "order_date": "2025-11-01",
  "expected_delivery_date": "2025-11-15",
  "notes": "Urgent order"
}
```

### GET `/orders/{id}`
Get a single order.

### PUT `/orders/{id}`
Update an order.

### DELETE `/orders/{id}`
Delete an order.

### POST `/orders/{id}/status`
Update order status.

**Body:**
```json
{
  "status": "confirmed"
}
```

**Status options:** `pending`, `confirmed`, `in_production`, `ready`, `delivered`, `cancelled`

### POST `/orders/batch`
Batch create orders.

**Body:**
```json
{
  "orders": [
    {
      "product_id": 1,
      "customer_id": 1,
      "qty": 10,
      "order_date": "2025-11-01"
    }
  ]
}
```

---

## Sales Endpoints

### GET `/sales`
Get all sales.

**Response:**
```json
{
  "sales": [
    {
      "id": 1,
      "order": {...},
      "customer": {...},
      "product": {...},
      "qty": 10.000,
      "unit_price": 100.00,
      "total_amount": 1000.00,
      "sale_date": "2025-11-01",
      "payment_status": "paid"
    }
  ]
}
```

### POST `/sales`
Create a new sale.

**Body:**
```json
{
  "order_id": 1,
  "customer_id": 1,
  "product_id": 1,
  "qty": 10,
  "unit_price": 100,
  "sale_date": "2025-11-01",
  "payment_status": "pending",
  "notes": "Invoice sent"
}
```

### GET `/sales/{id}`
Get a single sale.

### PUT `/sales/{id}`
Update a sale.

### DELETE `/sales/{id}`
Delete a sale.

### POST `/sales/{id}/payment-status`
Update payment status.

**Body:**
```json
{
  "payment_status": "paid"
}
```

**Payment status options:** `pending`, `partial`, `paid`

### POST `/sales/batch`
Batch create sales.

**Body:**
```json
{
  "sales": [
    {
      "product_id": 1,
      "customer_id": 1,
      "qty": 10,
      "unit_price": 100,
      "sale_date": "2025-11-01"
    }
  ]
}
```

---

## ML Analytics Endpoints

### GET `/analytics/status`
Check if ML service is available.

### POST `/analytics/forecast`
Get demand forecast.

### GET `/analytics/reorder-alerts`
Get AI-powered reorder alerts.

### GET `/analytics/models`
Get list of trained models.

### GET `/analytics/warehouses`
Get available warehouses.

### GET `/analytics/categories`
Get product categories.

### POST `/analytics/retrain`
Retrain ML models.

---

## Error Responses

All endpoints return appropriate HTTP status codes:

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

**Error format:**
```json
{
  "message": "Error description",
  "errors": {
    "field": ["Validation error message"]
  }
}
```
