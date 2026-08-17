# Orah PIMS: Align Modern Stack with Legacy Business Logic

## Problem Summary

The legacy vanilla PHP codebase implements a **periodic inventory management** workflow:

1. **RM Master** → Define raw materials, units, and customers ✅ (done)
2. **Add Product** → Create finished products with a **Bill of Materials (BOM)** linking each product to its raw materials + qty-per-unit ⚠️ (partially done — BOM management missing from controller & frontend)
3. **RM Purchase** → Record raw material inventory purchases ✅ (done)
4. **Order Book** → Record incoming orders; show **material competency** (required vs. available) ❌ (frontend is a static shell)
5. **Sales Book** → **Escalate** a ready order into a sale; **deduct inventory** by inserting negative `rm_purchase` records ❌ (frontend is a static shell; backend has no inventory deduction logic)

The modern Laravel + React implementation has models, migrations, and controllers scaffolded, but **three critical gaps** remain:

| Area | Status | Gap |
|------|--------|-----|
| Product BOM management | `product_bom` table exists, `ProductBom` model is empty shell | No controller routes to CRUD BOM; no frontend BOM editor on Product page |
| Order Book + Inventory Calculation | `OrderBookController` is a generic CRUD | No `calculate` endpoint; frontend is a hardcoded static form with no API calls |
| Sales Book Escalation + Material Deduction | `SalesBookController` is a generic CRUD | No order-ticket picker; no inventory check; no material deduction on escalation |

---

## Open Questions

> [!IMPORTANT]
> **Legacy dynamic table anti-pattern**: The old code creates a new MySQL **table per product** (e.g. a table called `gulab_jamun` with the BOM rows). The new schema correctly uses the `product_bom` table with a `product_id` FK instead. This plan uses the normalized `product_bom` approach. Please confirm this is acceptable.

> [!IMPORTANT]
> **Inventory deduction strategy**: The legacy code inserts **negative-quantity rows** into `rm_purchase` to deduct stock when a sale is made. The new schema also has a `stock_movements` table designed for proper audit trails. Should we:
> - **(A)** Follow the legacy approach (negative `rm_purchase` rows) for simplicity and parity? 
> - **(B)** Use the cleaner `stock_movements` table for deductions and keep `rm_purchase` purely for positive purchases?
> 
> **Recommendation**: Option B — use `stock_movements` with `movement_type = 'consumption'`. This preserves audit integrity and avoids corrupting purchase records. The inventory calculation would then be: `SUM(rm_purchase.qty) - SUM(stock_movements.qty_change WHERE type='consumption')`.

> [!NOTE]
> **Order status flow**: The legacy code uses `pending` / `partial` statuses on orders, and creates a new order row for remaining qty on partial dispatch. The new schema supports `pending → confirmed → in_production → ready → delivered → cancelled`. This plan keeps the new status set but implements the partial-dispatch logic from the legacy system.

---

## Proposed Changes

### Phase 1: Backend — BOM & Inventory Engine

#### [MODIFY] [ProductBom.php](file:///opt/lampp/htdocs/PIMS/backend/app/Models/ProductBom.php)
- Fill out the empty model: define `$table`, `$fillable`, `$casts`, and relationships to `FinishedProduct`, `RawMaterial`, `RawMaterialUnit`

#### [MODIFY] [FinishedProduct.php](file:///opt/lampp/htdocs/PIMS/backend/app/Models/FinishedProduct.php)
- Add `bomItems()` HasMany relationship to `ProductBom`

#### [MODIFY] [FinishedProductController.php](file:///opt/lampp/htdocs/PIMS/backend/app/Http/Controllers/Api/FinishedProductController.php)
- Override `store()` to accept `bom[]` array alongside product data (matching the legacy "Create Finished Product" form which submits material/qty/unit rows)
- Add `getBom($id)` endpoint returning BOM items with material name and unit name eagerly loaded
- Add `updateBom($id)` endpoint to replace BOM entries for a product (sync)
- Update `index()` to eager-load `bomItems.material` and `bomItems.unit`

#### [MODIFY] [OrderBookController.php](file:///opt/lampp/htdocs/PIMS/backend/app/Http/Controllers/Api/OrderBookController.php)
- Add `calculateMaterialRequirement(Request $request)` endpoint that:
  1. Accepts an array of product names (or IDs) from pending/partial orders
  2. For each product, fetches BOM from `product_bom` (replaces the legacy dynamic-table query)
  3. Sums total ordered qty from `order_book` for those products
  4. Multiplies BOM `qty_required × total_ordered_qty` = required qty per material
  5. Fetches available qty from `SUM(rm_purchase.qty)` minus `SUM(stock_movements.qty_change)` per material
  6. Returns `{ products: { [name]: [{ material, requiredQty, availableQty, difference }] } }` — matching the legacy JSON shape

#### [MODIFY] [SalesBookController.php](file:///opt/lampp/htdocs/PIMS/backend/app/Http/Controllers/Api/SalesBookController.php)
- Add `escalateOrder(Request $request)` endpoint that:
  1. Accepts `order_id`, `sales_date`, `dispatched_qty`
  2. Fetches the order and its product's BOM
  3. Calculates material requirements for the dispatched qty
  4. Checks material competency (available >= required)
  5. Creates a `sales_book` entry linked to the order
  6. Creates `stock_movements` entries (type `consumption`, reference `sale`) to deduct materials
  7. If `dispatched_qty < order.qty`, creates a new order row for remaining qty (partial dispatch)
  8. Updates original order status to `delivered` (or `partial` if partial dispatch)
  9. Returns the sale + inventory deduction summary

---

### Phase 2: Backend Routes

#### [MODIFY] [api.php](file:///opt/lampp/htdocs/PIMS/backend/routes/api.php)
- Add to products group:
  - `GET /products/{id}/bom` → `getBom`
  - `PUT /products/{id}/bom` → `updateBom`
- Add to orders group:
  - `POST /orders/calculate` → `calculateMaterialRequirement`
- Add to sales group:
  - `POST /sales/escalate` → `escalateOrder`

---

### Phase 3: Frontend — Order Book Page

#### [NEW] [OrderBook.tsx](file:///opt/lampp/htdocs/PIMS/frontend/src/pages/OrderBook.tsx)
Replace the static `OrderBook` component currently inline in `App.tsx` with a full-featured page:

- **Order Entry Form** (top half):
  - Product dropdown (populated from `GET /products`)
  - Customer dropdown (populated from `GET /customers`)
  - Qty input, Order Date input
  - Dynamic "Add Row" for bulk order entry
  - Submit calls `POST /orders` or `POST /orders/batch`

- **Submitted Orders Table** (middle):
  - Fetches `GET /orders` filtered to `pending`/`partial` status
  - Displays order_date, product_name, qty, billing_name, status
  - Each row has an "Escalate to Sales" button

- **Material Competency Panel** (bottom):
  - "Show Calculation" button calls `POST /orders/calculate`
  - Renders inventory status table per product with material, required, available, difference, status (green "Competent" / red "Need X more")

---

### Phase 4: Frontend — Sales Book Page

#### [NEW] [SalesBook.tsx](file:///opt/lampp/htdocs/PIMS/frontend/src/pages/SalesBook.tsx)
Replace the static `SalesBook` component currently inline in `App.tsx`:

- **Order Ticket Selector**: Dropdown populated from `GET /orders` (pending/ready orders)
- On selecting a ticket, auto-populate: product name, qty, billing name, sales date input
- User can adjust dispatched qty (for partial dispatch)
- Submit calls `POST /sales/escalate`
- Shows material competency check before dispatch
- **Submitted Sales Table**: Fetches `GET /sales` to show historical dispatches

---

### Phase 5: Frontend — Product BOM Editor

#### [MODIFY] [ProductManagement.tsx](file:///opt/lampp/htdocs/PIMS/frontend/src/pages/ProductManagement.tsx)
- Extend the product creation/edit form to include a **BOM section**:
  - Dynamic table: Material (dropdown from RM master), Qty, Unit (dropdown from units)
  - Add/remove rows
  - On product create, send `{ product: {...}, bom: [{material_id, qty_required, unit_id}] }`
  - On product view/edit, show existing BOM from `GET /products/{id}/bom`

---

### Phase 6: Frontend Wiring

#### [MODIFY] [App.tsx](file:///opt/lampp/htdocs/PIMS/frontend/src/App.tsx)
- Remove inline `OrderBook` and `SalesBook` components
- Import new page components from `pages/`
- Update routes

#### [MODIFY] [pages/index.ts](file:///opt/lampp/htdocs/PIMS/frontend/src/pages/index.ts)
- Export `OrderBook` and `SalesBook`

#### [MODIFY] [services/index.ts](file:///opt/lampp/htdocs/PIMS/frontend/src/services/index.ts)
- Add `orderService.calculateMaterialRequirement(productIds[])`
- Add `salesService.escalateOrder(orderId, salesDate, dispatchedQty)`
- Add `productService.getBom(productId)` and `productService.updateBom(productId, bom[])`
- Fix `purchaseService` base URL (currently `/purchases` but route is `/rm-purchases`)

---

## Verification Plan

### Automated Tests
1. `php artisan migrate:fresh --seed` — verify no errors
2. Manual API calls via browser subagent:
   - Create a product with BOM
   - Create RM purchases
   - Create an order
   - Hit `POST /orders/calculate` → verify competency JSON
   - Hit `POST /sales/escalate` → verify sale created + stock_movements deducted
   - Verify partial dispatch creates residual order

### Manual Verification  
- Run frontend dev server, walk through the full flow:
  1. RM Master → add material + unit
  2. Products → create product with BOM
  3. RM Purchase → purchase materials
  4. Order Book → create order → click "Show Calculation" → see green/red status
  5. Sales Book → select order ticket → escalate → verify inventory deducted
