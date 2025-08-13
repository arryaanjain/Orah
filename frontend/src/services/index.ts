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
  DashboardStats,
  ApiResponse 
} from '../types';

// Authentication Services
export const authService = {
  login: async (credentials: { company_name: string; email: string; password: string }) => {
    const response = await apiService.post<ApiResponse<{ user: User; token: string; company: Company }>>('/auth/login', credentials);
    return response.data;
  },

  register: async (data: { 
    company_name: string; 
    name: string; 
    email: string; 
    password: string; 
    password_confirmation: string;
  }) => {
    const response = await apiService.post<ApiResponse<{ user: User; token: string; company: Company }>>('/auth/register', data);
    return response.data;
  },

  logout: async () => {
    const response = await apiService.post<ApiResponse<null>>('/auth/logout');
    return response.data;
  },

  me: async () => {
    const response = await apiService.get<ApiResponse<{ user: User; company: Company }>>('/auth/me');
    return response.data;
  },
};

// Dashboard Services
export const dashboardService = {
  getStats: async () => {
    const response = await apiService.get<ApiResponse<DashboardStats>>('/dashboard/stats');
    return response.data;
  },
};

// Raw Material Services
export const rawMaterialService = {
  getAll: async () => {
    const response = await apiService.get<ApiResponse<RawMaterial[]>>('/raw-materials');
    return response.data;
  },

  create: async (data: Omit<RawMaterial, 'id' | 'company_id' | 'user_id' | 'created_at' | 'updated_at'>) => {
    const response = await apiService.post<ApiResponse<RawMaterial>>('/raw-materials', data);
    return response.data;
  },

  update: async (id: number, data: Partial<RawMaterial>) => {
    const response = await apiService.put<ApiResponse<RawMaterial>>(`/raw-materials/${id}`, data);
    return response.data;
  },

  delete: async (id: number) => {
    const response = await apiService.delete<ApiResponse<null>>(`/raw-materials/${id}`);
    return response.data;
  },
};

// Unit Services
export const unitService = {
  getByMaterial: async (materialId: number) => {
    const response = await apiService.get<ApiResponse<Unit[]>>(`/raw-materials/${materialId}/units`);
    return response.data;
  },

  create: async (materialId: number, data: Omit<Unit, 'id' | 'company_id' | 'user_id' | 'material_id' | 'created_at'>) => {
    const response = await apiService.post<ApiResponse<Unit>>(`/raw-materials/${materialId}/units`, data);
    return response.data;
  },

  update: async (id: number, data: Partial<Unit>) => {
    const response = await apiService.put<ApiResponse<Unit>>(`/units/${id}`, data);
    return response.data;
  },

  delete: async (id: number) => {
    const response = await apiService.delete<ApiResponse<null>>(`/units/${id}`);
    return response.data;
  },
};

// Purchase Services
export const purchaseService = {
  getAll: async () => {
    const response = await apiService.get<ApiResponse<RawMaterialPurchase[]>>('/purchases');
    return response.data;
  },

  create: async (data: Omit<RawMaterialPurchase, 'id' | 'company_id' | 'user_id' | 'created_at' | 'updated_at'>) => {
    const response = await apiService.post<ApiResponse<RawMaterialPurchase>>('/purchases', data);
    return response.data;
  },

  update: async (id: number, data: Partial<RawMaterialPurchase>) => {
    const response = await apiService.put<ApiResponse<RawMaterialPurchase>>(`/purchases/${id}`, data);
    return response.data;
  },

  delete: async (id: number) => {
    const response = await apiService.delete<ApiResponse<null>>(`/purchases/${id}`);
    return response.data;
  },
};

// Product Services
export const productService = {
  getAll: async () => {
    const response = await apiService.get<ApiResponse<FinishedProduct[]>>('/products');
    return response.data;
  },

  create: async (data: {
    product: Omit<FinishedProduct, 'id' | 'company_id' | 'user_id' | 'created_at' | 'updated_at'>;
    bom: Array<Omit<ProductBOM, 'id' | 'company_id' | 'user_id' | 'product_id' | 'created_at' | 'updated_at'>>;
  }) => {
    const response = await apiService.post<ApiResponse<FinishedProduct>>('/products', data);
    return response.data;
  },

  update: async (id: number, data: Partial<FinishedProduct>) => {
    const response = await apiService.put<ApiResponse<FinishedProduct>>(`/products/${id}`, data);
    return response.data;
  },

  delete: async (id: number) => {
    const response = await apiService.delete<ApiResponse<null>>(`/products/${id}`);
    return response.data;
  },

  getBOM: async (productId: number) => {
    const response = await apiService.get<ApiResponse<ProductBOM[]>>(`/products/${productId}/bom`);
    return response.data;
  },

  updateBOM: async (productId: number, bom: Array<Omit<ProductBOM, 'id' | 'company_id' | 'user_id' | 'product_id' | 'created_at' | 'updated_at'>>) => {
    const response = await apiService.put<ApiResponse<ProductBOM[]>>(`/products/${productId}/bom`, { bom });
    return response.data;
  },
};

// Customer Services
export const customerService = {
  getAll: async () => {
    const response = await apiService.get<ApiResponse<Customer[]>>('/customers');
    return response.data;
  },

  create: async (data: Omit<Customer, 'id' | 'company_id' | 'user_id' | 'created_at' | 'updated_at'>) => {
    const response = await apiService.post<ApiResponse<Customer>>('/customers', data);
    return response.data;
  },

  update: async (id: number, data: Partial<Customer>) => {
    const response = await apiService.put<ApiResponse<Customer>>(`/customers/${id}`, data);
    return response.data;
  },

  delete: async (id: number) => {
    const response = await apiService.delete<ApiResponse<null>>(`/customers/${id}`);
    return response.data;
  },
};

// Order Services
export const orderService = {
  getAll: async () => {
    const response = await apiService.get<ApiResponse<Order[]>>('/orders');
    return response.data;
  },

  create: async (data: Omit<Order, 'id' | 'company_id' | 'user_id' | 'created_at' | 'updated_at'>) => {
    const response = await apiService.post<ApiResponse<Order>>('/orders', data);
    return response.data;
  },

  update: async (id: number, data: Partial<Order>) => {
    const response = await apiService.put<ApiResponse<Order>>(`/orders/${id}`, data);
    return response.data;
  },

  delete: async (id: number) => {
    const response = await apiService.delete<ApiResponse<null>>(`/orders/${id}`);
    return response.data;
  },

  calculateMaterialRequirement: async (orderId: number) => {
    const response = await apiService.post<ApiResponse<{
      order_id: number;
      product: string;
      inventory_status: InventoryStatus[];
    }>>('/orders/calculate-material-requirement', { order_id: orderId });
    return response.data;
  },
};

// Sales Services
export const salesService = {
  getAll: async () => {
    const response = await apiService.get<ApiResponse<Sale[]>>('/sales');
    return response.data;
  },

  create: async (data: Omit<Sale, 'id' | 'company_id' | 'user_id' | 'created_at' | 'updated_at'>) => {
    const response = await apiService.post<ApiResponse<Sale>>('/sales', data);
    return response.data;
  },

  update: async (id: number, data: Partial<Sale>) => {
    const response = await apiService.put<ApiResponse<Sale>>(`/sales/${id}`, data);
    return response.data;
  },

  delete: async (id: number) => {
    const response = await apiService.delete<ApiResponse<null>>(`/sales/${id}`);
    return response.data;
  },
};

// Stock Movement Services
export const stockMovementService = {
  getAll: async () => {
    const response = await apiService.get<ApiResponse<StockMovement[]>>('/stock-movements');
    return response.data;
  },

  getByMaterial: async (materialId: number) => {
    const response = await apiService.get<ApiResponse<StockMovement[]>>(`/raw-materials/${materialId}/movements`);
    return response.data;
  },

  create: async (data: Omit<StockMovement, 'id' | 'company_id' | 'user_id' | 'movement_date'>) => {
    const response = await apiService.post<ApiResponse<StockMovement>>('/stock-movements', data);
    return response.data;
  },
};
