# Walkthrough — Orah Inventory System Modernization

We have completed the modernization and migration of the legacy PHP Orah inventory management system to the modern Laravel/React architecture.

---

## 1. Database Schema & Eloquent Models

- **`product_bom`**: Replaced dynamic table creation per product with a normalized `ProductBom` model mapping `product_id`, `material_id`, `qty_required`, and `unit_id`.
- **`stock_movements`**: Implemented audit logging for material stock movement with `StockMovement` model. Deductions are recorded as `movement_type = 'consumption'`.
- **`FinishedProduct`**: Configured `bomItems()` relationship (`hasMany(ProductBom::class)`).

---

## 2. Controller & Business Logic

### FinishedProductController
- Implemented `getBom()` and `updateBom()` methods to fetch and replace product recipes (BOM).
- `store()` and `update()` allow saving BOM during product creation or editing.

### OrderBookController
- Implemented `calculateMaterialRequirement()`:
  $$\text{Required Qty} = \text{BOM qty\_required} \times \text{Total Ordered Qty}$$
  $$\text{Available Qty} = \sum \text{rm\_purchase.qty} - \sum \text{stock\_movements.qty\_change (type=consumption)}$$
  $$\text{Difference} = \text{Available Qty} - \text{Required Qty}$$
- Implemented `calculateOrderMaterial($id)` for checking material competency for a specific order.

### SalesBookController
- Implemented `escalateOrder()`:
  1. Validates material competency before dispatching.
  2. Records entry in `sales_book`.
  3. Creates `stock_movements` (consumption) records to deduct raw materials based on BOM recipe.
  4. **Partial Dispatch Support**: If `dispatched_qty < order.qty`, automatically creates a new residual sub-order for the remaining quantity and updates the original order status to `delivered`.

---

## 3. Frontend Integration & Pages

- **`types/index.ts`**: Defined interfaces for `ProductBOM`, `StockMovement`, `InventoryStatus`, `CalculationResponse`, and `EscalationResponse`.
- **`services/index.ts`**: Connected `productService`, `orderService`, `salesService`, and `stockMovementService` to the backend REST endpoints.
- **`ProductManagement.tsx`**: Added an interactive Bill of Materials recipe editor in the product form and a BOM formula viewing modal.
- **`OrderBook.tsx`**: Full order management with multi-row entry, order list, and real-time material competency calculation engine UI.
- **`SalesBook.tsx`**: Escalate pending orders with order ticket selection, live material availability check, partial dispatch support (residual order generation), and sales history audit table.

---

## 4. Verification

- **Backend Route Validation**: Checked using `php artisan route:list` — 67 API routes compiled cleanly.
- **Database Migration & Seeding**: Ran `php artisan migrate:fresh --seed` successfully.
- **Frontend Production Build**: Ran `npm run build` — `tsc -b && vite build` bundled with **0 errors**.
