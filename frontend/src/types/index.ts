// Core Types for PIMS Application
export interface User {
  id: number;
  company_id: number;
  name: string;
  email: string;
  created_at: string;
  updated_at: string;
}

export interface Company {
  id: number;
  name: string;
  email?: string;
  created_at: string;
  updated_at: string;
}

export interface RawMaterial {
  id: number;
  company_id: number;
  user_id: number;
  material: string;
  description?: string;
  base_unit: string;
  minimum_stock: number;
  created_at: string;
  updated_at: string;
}

export interface Unit {
  id: number;
  company_id: number;
  user_id: number;
  material_id: number;
  unit_name: string;
  conversion_factor: number;
  created_at: string;
}

export interface RawMaterialPurchase {
  id: number;
  company_id: number;
  user_id: number;
  material_id: number;
  supplier_name?: string;
  purchase_date: string;
  qty: number;
  unit_id: number;
  rate: number;
  total_amount: number;
  batch_number?: string;
  expiry_date?: string;
  created_at: string;
  updated_at: string;
}

export interface FinishedProduct {
  id: number;
  company_id: number;
  user_id: number;
  product_name: string;
  description?: string;
  base_unit: string;
  selling_price: number;
  minimum_stock: number;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface ProductBOM {
  id: number;
  company_id: number;
  user_id: number;
  product_id: number;
  material_id: number;
  qty_required: number;
  unit_id: number;
  created_at: string;
  updated_at: string;
  // Joined fields
  material_name?: string;
  unit_name?: string;
}

export interface Customer {
  id: number;
  company_id: number;
  user_id: number;
  name: string;
  email?: string;
  phone?: string;
  address?: string;
  created_at: string;
  updated_at: string;
}

export type OrderStatus = 'pending' | 'confirmed' | 'in_production' | 'ready' | 'delivered' | 'cancelled';

export interface Order {
  id: number;
  company_id: number;
  user_id: number;
  customer_id?: number;
  product_id: number;
  qty: number;
  unit_price: number;
  total_amount: number;
  order_date: string;
  expected_delivery_date?: string;
  status: OrderStatus;
  notes?: string;
  created_at: string;
  updated_at: string;
  // Joined fields
  customer_name?: string;
  product_name?: string;
}

export type PaymentStatus = 'pending' | 'partial' | 'paid';

export interface Sale {
  id: number;
  company_id: number;
  user_id: number;
  order_id?: number;
  customer_id?: number;
  product_id: number;
  qty: number;
  unit_price: number;
  total_amount: number;
  sale_date: string;
  payment_status: PaymentStatus;
  notes?: string;
  created_at: string;
  updated_at: string;
  // Joined fields
  customer_name?: string;
  product_name?: string;
}

export type MovementType = 'purchase' | 'consumption' | 'adjustment' | 'return';
export type ReferenceType = 'purchase' | 'sale' | 'production' | 'manual';

export interface StockMovement {
  id: number;
  company_id: number;
  user_id: number;
  material_id: number;
  movement_type: MovementType;
  reference_type: ReferenceType;
  reference_id?: number;
  qty_change: number;
  unit_id: number;
  notes?: string;
  movement_date: string;
  // Joined fields
  material_name?: string;
  unit_name?: string;
}

export interface InventoryStatus {
  material: string;
  requiredQty: number;
  availableQty: number;
  difference: number;
  unit: string;
}

export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  message?: string;
  errors?: string[];
}

export interface DashboardStats {
  totalProducts: number;
  totalOrders: number;
  totalSales: number;
  lowStockMaterials: number;
  pendingOrders: number;
  recentActivity: Array<{
    id: number;
    type: string;
    description: string;
    date: string;
  }>;
}
