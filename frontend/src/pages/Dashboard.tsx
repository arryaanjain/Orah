import { 
  Package, 
  Truck, 
  AlertTriangle, 
  TrendingUp, 
  Calendar,
  Users,
  Activity,
  CheckCircle,
  XCircle,
  ClipboardList
} from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { useState, useEffect } from 'react';
import { apiService } from '../services/api';

interface Stats {
  total_products: number;
  total_orders: number;
  total_sales: number;
  low_stock_items: number;
  monthly_sales: number;
  total_customers: number;
}

interface Changes {
  products: string;
  orders: string;
  sales: string;
  low_stock: string;
}

interface RecentActivity {
  id: string;
  type: 'order' | 'sale' | 'stock' | 'alert';
  description: string;
  time: string;
  timestamp: string;
}

interface AnalyticsStatus {
  available: boolean;
  message?: string;
}

export function Dashboard() {
  const navigate = useNavigate();
  const [analyticsAvailable, setAnalyticsAvailable] = useState<boolean | null>(null);
  const [loadingAnalytics, setLoadingAnalytics] = useState(true);
  
  // Dashboard data state
  const [stats, setStats] = useState<Stats>({
    total_products: 0,
    total_orders: 0,
    total_sales: 0,
    low_stock_items: 0,
    monthly_sales: 0,
    total_customers: 0,
  });
  const [changes, setChanges] = useState<Changes>({
    products: '+0%',
    orders: '+0%',
    sales: '+0%',
    low_stock: '+0%',
  });
  const [recentActivities, setRecentActivities] = useState<RecentActivity[]>([]);
  const [loadingData, setLoadingData] = useState(true);

  useEffect(() => {
    checkAnalyticsStatus();
    loadDashboardData();
  }, []);

  const loadDashboardData = async () => {
    try {
      setLoadingData(true);
      
      const statsResponse = await apiService.get<{ stats: Stats; changes: Changes }>('/dashboard/stats');
      if (statsResponse.data) {
        setStats(statsResponse.data.stats || {
          total_products: 0,
          total_orders: 0,
          total_sales: 0,
          low_stock_items: 0,
          monthly_sales: 0,
          total_customers: 0,
        });
        setChanges(statsResponse.data.changes || {
          products: '+0%',
          orders: '+0%',
          sales: '+0%',
          low_stock: '+0%',
        });
      }
      
      const activitiesResponse = await apiService.get<{ activities: RecentActivity[] }>('/dashboard/recent-activities');
      if (activitiesResponse.data) {
        setRecentActivities(activitiesResponse.data.activities || []);
      }
    } catch (err) {
      console.error('Error loading dashboard data:', err);
    } finally {
      setLoadingData(false);
    }
  };

  const checkAnalyticsStatus = async () => {
    try {
      const statusResponse = await apiService.get<AnalyticsStatus>('/analytics/status');
      const isAvailable = statusResponse.data?.available ?? false;
      setAnalyticsAvailable(isAvailable);
    } catch (err: any) {
      console.error('Error checking analytics:', err);
      setAnalyticsAvailable(false);
    } finally {
      setLoadingAnalytics(false);
    }
  };

  const statCards = [
    {
      title: 'Total Products',
      value: stats.total_products,
      icon: Package,
      color: 'bg-blue-500',
      change: changes.products,
    },
    {
      title: 'Active Orders',
      value: stats.total_orders,
      icon: ClipboardList,
      color: 'bg-green-500',
      change: changes.orders,
    },
    {
      title: 'Customers Registered',
      value: stats.total_customers || 0,
      icon: Users,
      color: 'bg-purple-500',
      change: '+0%',
    },
    {
      title: 'Low Stock Materials',
      value: stats.low_stock_items,
      icon: AlertTriangle,
      color: 'bg-red-500',
      change: changes.low_stock,
    },
  ];

  return (
    <div className="p-4 lg:p-6 space-y-4 lg:space-y-6">
      {/* Header */}
      <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl lg:text-2xl font-bold text-gray-900">Orah Dashboard</h1>
          <p className="text-sm lg:text-base text-gray-600 mt-1">Real-time periodic inventory management and competency tracking.</p>
        </div>
        <div className="flex items-center space-x-3">
          <Calendar className="h-4 lg:h-5 w-4 lg:w-5 text-gray-400" />
          <span className="text-xs lg:text-sm text-gray-600">
            {new Date().toLocaleDateString()}
          </span>
        </div>
      </div>

      {/* Analytics Status Banner */}
      <div className={`rounded-lg border-2 p-4 ${
        loadingAnalytics 
          ? 'bg-gray-50 border-gray-200' 
          : analyticsAvailable 
            ? 'bg-green-50 border-green-200' 
            : 'bg-yellow-50 border-yellow-200'
      }`}>
        <div className="flex items-center justify-between">
          <div className="flex items-center">
            {loadingAnalytics ? (
              <div className="animate-spin h-5 w-5 border-2 border-gray-400 border-t-transparent rounded-full mr-3"></div>
            ) : analyticsAvailable ? (
              <CheckCircle className="h-5 w-5 text-green-600 mr-3" />
            ) : (
              <XCircle className="h-5 w-5 text-yellow-600 mr-3" />
            )}
            <div>
              <h3 className="text-sm font-semibold text-gray-900 flex items-center">
                <Activity className="h-4 w-4 mr-2" />
                ML Demand Forecasting Engine
              </h3>
              <p className="text-xs text-gray-600 mt-1">
                {loadingAnalytics && 'Checking service status...'}
                {!loadingAnalytics && analyticsAvailable && 'Advanced forecasting and reorder alerts active'}
                {!loadingAnalytics && !analyticsAvailable && 'Periodic calculation active — ML forecasting backend offline'}
              </p>
            </div>
          </div>
          <span className={`px-3 py-1 rounded-full text-xs font-medium ${
            loadingAnalytics 
              ? 'bg-gray-200 text-gray-700' 
              : analyticsAvailable 
                ? 'bg-green-600 text-white' 
                : 'bg-yellow-600 text-white'
          }`}>
            {loadingAnalytics ? 'Checking...' : analyticsAvailable ? 'Available' : 'Standard Mode'}
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
          {loadingData ? (
            <div className="flex items-center justify-center py-8">
              <div className="animate-spin h-8 w-8 border-2 border-blue-500 border-t-transparent rounded-full"></div>
            </div>
          ) : recentActivities.length === 0 ? (
            <div className="text-center py-8 text-gray-500">
              <p>No recent activities</p>
            </div>
          ) : (
            <div className="space-y-3 lg:space-y-4">
              {recentActivities.map((activity) => (
                <div key={activity.id} className="flex items-start space-x-3">
                  <div className="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                  <div className="flex-1">
                    <p className="text-sm text-gray-900">{activity.description}</p>
                    <p className="text-xs text-gray-500">{activity.time}</p>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Quick Actions */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
          <h3 className="text-base lg:text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 lg:gap-4">
            <button 
              onClick={() => navigate('/products')}
              className="p-3 lg:p-4 border border-gray-200 rounded-lg hover:bg-blue-50 transition-colors text-left group"
            >
              <Package className="h-6 lg:h-8 w-6 lg:w-8 text-blue-500 mb-2 group-hover:scale-110 transition-transform" />
              <h4 className="font-medium text-gray-900 text-sm lg:text-base">Products & BOM</h4>
              <p className="text-xs lg:text-sm text-gray-600">Manage catalog & recipes</p>
            </button>
            
            <button 
              onClick={() => navigate('/orders')}
              className="p-3 lg:p-4 border border-gray-200 rounded-lg hover:bg-green-50 transition-colors text-left group"
            >
              <ClipboardList className="h-6 lg:h-8 w-6 lg:w-8 text-green-500 mb-2 group-hover:scale-110 transition-transform" />
              <h4 className="font-medium text-gray-900 text-sm lg:text-base">Order Book</h4>
              <p className="text-xs lg:text-sm text-gray-600">Check material competency</p>
            </button>
            
            <button 
              onClick={() => navigate('/sales')}
              className="p-3 lg:p-4 border border-gray-200 rounded-lg hover:bg-purple-50 transition-colors text-left group"
            >
              <Truck className="h-6 lg:h-8 w-6 lg:w-8 text-purple-500 mb-2 group-hover:scale-110 transition-transform" />
              <h4 className="font-medium text-gray-900 text-sm lg:text-base">Sales Book</h4>
              <p className="text-xs lg:text-sm text-gray-600">Escalate & dispatch sales</p>
            </button>
            
            <button 
              onClick={() => navigate('/customers')}
              className="p-3 lg:p-4 border border-gray-200 rounded-lg hover:bg-orange-50 transition-colors text-left group"
            >
              <Users className="h-6 lg:h-8 w-6 lg:w-8 text-orange-500 mb-2 group-hover:scale-110 transition-transform" />
              <h4 className="font-medium text-gray-900 text-sm lg:text-base">Customers</h4>
              <p className="text-xs lg:text-sm text-gray-600">Billing details & contacts</p>
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
