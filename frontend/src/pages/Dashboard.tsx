import { 
  Package, 
  ShoppingCart, 
  Truck, 
  AlertTriangle, 
  TrendingUp, 
  Calendar,
  DollarSign,
  Users
} from 'lucide-react';
import { useNavigate } from 'react-router-dom';

// Mock data for UI demonstration
const mockStats = {
  totalProducts: 156,
  totalOrders: 43,
  totalSales: 89,
  lowStockItems: 12,
  monthlySales: 45000,
  totalCustomers: 28,
};

const mockRecentActivities = [
  { id: 1, type: 'order', description: 'New order #1234 received', time: '2 hours ago' },
  { id: 2, type: 'stock', description: 'Stock updated for Product A', time: '4 hours ago' },
  { id: 3, type: 'sale', description: 'Sale completed for Customer XYZ', time: '6 hours ago' },
  { id: 4, type: 'alert', description: 'Low stock alert for Raw Material B', time: '8 hours ago' },
];

export function Dashboard() {
  const navigate = useNavigate();

  const statCards = [
    {
      title: 'Total Products',
      value: mockStats.totalProducts,
      icon: Package,
      color: 'bg-blue-500',
      change: '+12%',
    },
    {
      title: 'Active Orders',
      value: mockStats.totalOrders,
      icon: ShoppingCart,
      color: 'bg-green-500',
      change: '+8%',
    },
    {
      title: 'Monthly Sales',
      value: `$${mockStats.monthlySales.toLocaleString()}`,
      icon: DollarSign,
      color: 'bg-purple-500',
      change: '+15%',
    },
    {
      title: 'Low Stock Items',
      value: mockStats.lowStockItems,
      icon: AlertTriangle,
      color: 'bg-red-500',
      change: '-3%',
    },
  ];

  return (
    <div className="p-4 lg:p-6 space-y-4 lg:space-y-6">
      {/* Header */}
      <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl lg:text-2xl font-bold text-gray-900">Dashboard</h1>
          <p className="text-sm lg:text-base text-gray-600 mt-1">Welcome back! Here's what's happening in your business.</p>
        </div>
        <div className="flex items-center space-x-3">
          <Calendar className="h-4 lg:h-5 w-4 lg:w-5 text-gray-400" />
          <span className="text-xs lg:text-sm text-gray-600">
            {new Date().toLocaleDateString()}
          </span>
        </div>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        {statCards.map((stat, index) => (
          <div key={index} className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-xs lg:text-sm text-gray-600 mb-1">{stat.title}</p>
                <p className="text-lg lg:text-2xl font-bold text-gray-900">{stat.value}</p>
                <div className="flex items-center mt-2">
                  <TrendingUp className="h-3 lg:h-4 w-3 lg:w-4 text-green-500 mr-1" />
                  <span className="text-xs lg:text-sm text-green-600">{stat.change}</span>
                  <span className="text-xs lg:text-sm text-gray-500 ml-1">vs last month</span>
                </div>
              </div>
              <div className={`p-2 lg:p-3 rounded-full ${stat.color}`}>
                <stat.icon className="h-5 lg:h-6 w-5 lg:w-6 text-white" />
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Content Grid */}
      <div className="grid grid-cols-1 xl:grid-cols-2 gap-4 lg:gap-6">
        {/* Recent Activity */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
          <h3 className="text-base lg:text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
          <div className="space-y-3 lg:space-y-4">
            {mockRecentActivities.map((activity) => (
              <div key={activity.id} className="flex items-start space-x-3">
                <div className="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                <div className="flex-1">
                  <p className="text-sm text-gray-900">{activity.description}</p>
                  <p className="text-xs text-gray-500">{activity.time}</p>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Quick Actions */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
          <h3 className="text-base lg:text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 lg:gap-4">
            <button 
              onClick={() => navigate('/products')}
              className="p-3 lg:p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-left"
            >
              <Package className="h-6 lg:h-8 w-6 lg:w-8 text-blue-500 mb-2" />
              <h4 className="font-medium text-gray-900 text-sm lg:text-base">Add Product</h4>
              <p className="text-xs lg:text-sm text-gray-600">Create new product</p>
            </button>
            
            <button 
              onClick={() => navigate('/purchases')}
              className="p-3 lg:p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-left"
            >
              <ShoppingCart className="h-6 lg:h-8 w-6 lg:w-8 text-green-500 mb-2" />
              <h4 className="font-medium text-gray-900 text-sm lg:text-base">New Purchase</h4>
              <p className="text-xs lg:text-sm text-gray-600">Record purchase</p>
            </button>
            
            <button 
              onClick={() => navigate('/orders')}
              className="p-3 lg:p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-left"
            >
              <Truck className="h-6 lg:h-8 w-6 lg:w-8 text-purple-500 mb-2" />
              <h4 className="font-medium text-gray-900 text-sm lg:text-base">Process Order</h4>
              <p className="text-xs lg:text-sm text-gray-600">Handle new order</p>
            </button>
            
            <button 
              onClick={() => navigate('/raw-materials')}
              className="p-3 lg:p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-left"
            >
              <Users className="h-6 lg:h-8 w-6 lg:w-8 text-orange-500 mb-2" />
              <h4 className="font-medium text-gray-900 text-sm lg:text-base">Raw Materials</h4>
              <p className="text-xs lg:text-sm text-gray-600">Manage materials</p>
            </button>
          </div>
        </div>
      </div>

      {/* Charts section */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
        <h3 className="text-base lg:text-lg font-semibold text-gray-900 mb-4">Sales Overview</h3>
        <div className="h-48 lg:h-64 flex items-center justify-center text-gray-500 bg-gray-50 rounded-lg">
          <div className="text-center">
            <TrendingUp className="h-8 lg:h-12 w-8 lg:w-12 mx-auto mb-2 text-gray-400" />
            <p className="text-sm lg:text-base">Chart implementation would go here</p>
            <p className="text-xs lg:text-sm">Connect to backend for real data visualization</p>
          </div>
        </div>
      </div>
    </div>
  );
}
