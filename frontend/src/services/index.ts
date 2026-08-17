import { apiService } from './api';
import type { 
  User, 
  Company, 
  RawMaterial, 
  Unit, 
  RawMaterialPurchase,
  FinishedProduct,
  ProductBOM,
  Customer,
  Order,
  Sale,
  StockMovement,
  InventoryStatus,
  CalculationResponse,
  EscalationResponse,
  DashboardStats,
} from '../types';

// Authentication Services
export const authService = {
  login: async (credentials: { company_name: string; email: string; password: string }) => {
    const response = await apiService.post<{ user: User; token: string; company: Company }>('/auth/login', credentials);
    return response.data;
  },

  register: async (data: { 
    company_name: string; 
    name: string; 
    email: string; 
    password: string; 
    password_confirmation: string;
  }) => {
    const response = await apiService.post<{ user: User; token: string; company: Company }>('/auth/register', data);
    return response.data;
  },

  logout: async () => {
    const response = await apiService.post('/auth/logout');
    return response.data;
  },

  me: async () => {
    const response = await apiService.get<{ user: User; company: Company }>('/auth/me');
    return response.data;
  },
};

// Dashboard Services
export const dashboardService = {
  getStats: async () => {
    const response = await apiService.get<DashboardStats>('/dashboard/stats');
    return response.data;
  },
};

// Raw Material Services
export const rawMaterialService = {
  getAll: async () => {
    const response = await apiService.get<{ materials: RawMaterial[] }>('/raw-materials');
    return response.data;
  },

  create: async (data: { material: string; description?: string; base_unit: string; minimum_stock?: number }) => {
    const response = await apiService.post<{ message: string; material: RawMaterial }>('/raw-materials', data);
    return response.data;
  },

  update: async (id: number, data: Partial<RawMaterial>) => {
    const response = await apiService.put<{ message: string; material: RawMaterial }>(`/raw-materials/${id}`, data);
    return response.data;
  },

  delete: async (id: number) => {
    const response = await apiService.delete<{ message: string }>(`/raw-materials/${id}`);
    return response.data;
  },
};

// Unit Services
export const unitService = {
  getByMaterial: async (materialId: number) => {
    const response = await apiService.get<{ units: Unit[] }>(`/raw-materials/${materialId}/units`);
    return response.data;
  },

  create: async (materialId: number, data: { unit_name: string; conversion_factor: number }) => {
    const response = await apiService.post<{ message: string; unit: Unit }>(`/raw-materials/${materialId}/units`, data);
    return response.data;
  },

  delete: async (materialId: number, unitId: number) => {
    const response = await apiService.delete<{ message: string }>(`/raw-materials/${materialId}/units/${unitId}`);
    return response.data;
  },
};

// Purchase Services
export const purchaseService = {
  getAll: async () => {
    const response = await apiService.get<{ purchases: RawMaterialPurchase[] }>('/rm-purchases');
    return response.data;
  },

  create: async (data: {
    material_id: number;
    supplier_name?: string;
    purchase_date: string;
    qty: number;
    unit_id: number;
    rate?: number;
    batch_number?: string;
    expiry_date?: string;
  }) => {
    const response = await apiService.post<{ message: string; purchase: RawMaterialPurchase }>('/rm-purchases', data);
    return response.data;
  },

  update: async (id: number, data: Partial<RawMaterialPurchase>) => {
    const response = await apiService.put<{ message: string; purchase: RawMaterialPurchase }>(`/rm-purchases/${id}`, data);
    return response.data;
  },

  delete: async (id: number) => {
    const response = await apiService.delete<{ message: string }>(`/rm-purchases/${id}`);
    return response.data;
  },
};

// Product Services
export const productService = {
  getAll: async () => {
    const response = await apiService.get<{ products: FinishedProduct[] }>('/products');
    return response.data;
  },

  create: async (data: {
    product_name: string;
    description?: string;
    base_unit: string;
    selling_price?: number;
    minimum_stock?: number;
    bom?: Array<{ material_id: number; qty_required: number; unit_id: number }>;
  }) => {
    const response = await apiService.post<{ message: string; product: FinishedProduct }>('/products', data);
    return response.data;
  },

  show: async (id: number) => {
    const response = await apiService.get<{ product: FinishedProduct }>(`/products/${id}`);
    return response.data;
  },

  update: async (id: number, data: Partial<FinishedProduct>) => {
    const response = await apiService.put<{ message: string; product: FinishedProduct }>(`/products/${id}`, data);
    return response.data;
  },

  delete: async (id: number) => {
    const response = await apiService.delete<{ message: string }>(`/products/${id}`);
    return response.data;
  },

  getBom: async (productId: number) => {
    const response = await apiService.get<{ product_id: number; product_name: string; bom: ProductBOM[] }>(`/products/${productId}/bom`);
    return response.data;
  },

  updateBom: async (productId: number, bom: Array<{ material_id: number; qty_required: number; unit_id: number }>) => {
    const response = await apiService.put<{ message: string; bom: ProductBOM[] }>(`/products/${productId}/bom`, { bom });
    return response.data;
  },
};

// Customer Services
export const customerService = {
  getAll: async () => {
    const response = await apiService.get<{ customers: Customer[] }>('/customers');
    return response.data;
  },

  create: async (data: Partial<Customer>) => {
    const response = await apiService.post<{ message: string; customer: Customer }>('/customers', data);
    return response.data;
  },

  update: async (id: number, data: Partial<Customer>) => {
    const response = await apiService.put<{ message: string; customer: Customer }>(`/customers/${id}`, data);
    return response.data;
  },

  delete: async (id: number) => {
    const response = await apiService.delete<{ message: string }>(`/customers/${id}`);
    return response.data;
  },
};

// Order Services
export const orderService = {
  getAll: async (status?: string) => {
    const params = status ? `?status=${status}` : '';
    const response = await apiService.get<{ orders: Order[] }>(`/orders${params}`);
    return response.data;
  },

  create: async (data: {
    product_id: number;
    customer_id?: number;
    qty: number;
    unit_price?: number;
    order_date: string;
    expected_delivery_date?: string;
    notes?: string;
  }) => {
    const response = await apiService.post<{ message: string; order: Order }>('/orders', data);
    return response.data;
  },

  batchCreate: async (orders: Array<{
    product_id: number;
    customer_id?: number;
    qty: number;
    order_date: string;
  }>) => {
    const response = await apiService.post<{ message: string; orders: Order[] }>('/orders/batch', { orders });
    return response.data;
  },

  update: async (id: number, data: Partial<Order>) => {
    const response = await apiService.put<{ message: string; order: Order }>(`/orders/${id}`, data);
    return response.data;
  },

  delete: async (id: number) => {
    const response = await apiService.delete<{ message: string }>(`/orders/${id}`);
    return response.data;
  },

  updateStatus: async (id: number, status: string) => {
    const response = await apiService.post<{ message: string; order: Order }>(`/orders/${id}/status`, { status });
    return response.data;
  },

  /** Calculate material requirements across all pending orders */
  calculateMaterialRequirement: async () => {
    const response = await apiService.post<CalculationResponse>('/orders/calculate');
    return response.data;
  },

  /** Calculate material requirement for a single order */
  calculateOrderMaterial: async (orderId: number) => {
    const response = await apiService.get<{
      order_id: number;
      product: string;
      order_qty: number;
      inventory_status: InventoryStatus[];
    }>(`/orders/${orderId}/material-check`);
    return response.data;
  },
};

// Sales Services
export const salesService = {
  getAll: async () => {
    const response = await apiService.get<{ sales: Sale[] }>('/sales');
    return response.data;
  },

  create: async (data: {
    order_id?: number;
    customer_id?: number;
    product_id: number;
    qty: number;
    unit_price: number;
    sale_date: string;
    payment_status?: string;
    notes?: string;
  }) => {
    const response = await apiService.post<{ message: string; sale: Sale }>('/sales', data);
    return response.data;
  },

  update: async (id: number, data: Partial<Sale>) => {
    const response = await apiService.put<{ message: string; sale: Sale }>(`/sales/${id}`, data);
    return response.data;
  },

  delete: async (id: number) => {
    const response = await apiService.delete<{ message: string }>(`/sales/${id}`);
    return response.data;
  },

  /** Escalate an order to sales book with inventory deduction */
  escalateOrder: async (data: {
    order_id: number;
    sales_date: string;
    dispatched_qty: number;
  }) => {
    const response = await apiService.post<EscalationResponse>('/sales/escalate', data);
    return response.data;
  },

  updatePaymentStatus: async (id: number, paymentStatus: string) => {
    const response = await apiService.post<{ message: string; sale: Sale }>(`/sales/${id}/payment-status`, { payment_status: paymentStatus });
    return response.data;
  },
};

// Stock Movement Services
export const stockMovementService = {
  getAll: async () => {
    const response = await apiService.get<{ movements: StockMovement[] }>('/stock-movements');
    return response.data;
  },

  getByMaterial: async (materialId: number) => {
    const response = await apiService.get<{ movements: StockMovement[] }>(`/raw-materials/${materialId}/movements`);
    return response.data;
  },
};
