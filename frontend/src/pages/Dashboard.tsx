import { 
  Package, 
  ShoppingCart, 
  Truck, 
  AlertTriangle, 
  TrendingUp, 
  Calendar,
  DollarSign,
  Users,
  Activity,
  CheckCircle,
  XCircle
} from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { useState, useEffect } from 'react';
import { api } from '../services/api';

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

interface Alert {
  warehouse_id: string;
  product_category: string;
  current_stock: number;
  reorder_level: number;
  predicted_stockout_date: string;
  days_until_stockout: number;
  predicted_daily_demand: number;
  severity: 'high' | 'medium' | 'low';
}

interface AnalyticsStatus {
  available: boolean;
  message?: string;
}

export function Dashboard() {
  const navigate = useNavigate();
  const [analyticsAvailable, setAnalyticsAvailable] = useState<boolean | null>(null);
  const [alerts, setAlerts] = useState<Alert[]>([]);
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
      
      // Load stats
      const statsResponse = await api.get<{ stats: Stats; changes: Changes }>('/dashboard/stats');
      setStats(statsResponse.data.stats);
      setChanges(statsResponse.data.changes);
      
      // Load recent activities
      const activitiesResponse = await api.get<{ activities: RecentActivity[] }>('/dashboard/recent-activities');
      setRecentActivities(activitiesResponse.data.activities);
    } catch (err) {
      console.error('Error loading dashboard data:', err);
    } finally {
      setLoadingData(false);
    }
  };

  const checkAnalyticsStatus = async () => {
    try {
      // Check analytics status
      const statusResponse = await api.get<AnalyticsStatus>('/analytics/status');
      const isAvailable = statusResponse.data.available;
      setAnalyticsAvailable(isAvailable);

      if (isAvailable) {
        // Load reorder alerts
        const alertsResponse = await api.get<{ available: boolean; data?: any; alerts?: Alert[] }>(
          '/analytics/reorder-alerts'
        );
        
        if (alertsResponse.data.available && alertsResponse.data.data) {
          setAlerts(alertsResponse.data.data.alerts || []);
        }
      }
    } catch (err: any) {
      console.error('Error checking analytics:', err);
      setAnalyticsAvailable(false);
    } finally {
      setLoadingAnalytics(false);
    }
  };

  const getSeverityColor = (severity: string) => {
    switch (severity) {
      case 'high':
        return 'border-red-200 bg-red-50';
      case 'medium':
        return 'border-yellow-200 bg-yellow-50';
      case 'low':
        return 'border-blue-200 bg-blue-50';
      default:
        return 'border-gray-200 bg-white';
    }
  };

  const getSeverityBadge = (severity: string) => {
    switch (severity) {
      case 'high':
        return 'bg-red-500 text-white';
      case 'medium':
        return 'bg-yellow-500 text-white';
      case 'low':
        return 'bg-blue-500 text-white';
      default:
        return 'bg-gray-500 text-white';
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
      icon: ShoppingCart,
      color: 'bg-green-500',
      change: changes.orders,
    },
    {
      title: 'Monthly Sales',
      value: `$${stats.monthly_sales.toLocaleString()}`,
      icon: DollarSign,
      color: 'bg-purple-500',
      change: changes.sales,
    },
    {
      title: 'Low Stock Items',
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

      {/* ML Analytics Status Banner */}
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
                ML Analytics
              </h3>
              <p className="text-xs text-gray-600 mt-1">
                {loadingAnalytics && 'Checking service status...'}
                {!loadingAnalytics && analyticsAvailable && 'Advanced forecasting and reorder alerts active'}
                {!loadingAnalytics && !analyticsAvailable && 'Service unavailable - Using basic inventory tracking'}
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
            {loadingAnalytics ? 'Checking...' : analyticsAvailable ? 'Available' : 'Unavailable'}
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

      {/* ML Reorder Alerts Section */}
      {!loadingAnalytics && analyticsAvailable && (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <div className="px-4 lg:px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-red-50 to-orange-50">
            <h3 className="text-base lg:text-lg font-semibold text-gray-900 flex items-center">
              <AlertTriangle className="h-5 w-5 mr-2 text-red-500" />
              AI-Powered Reorder Alerts
              {alerts.length > 0 && (
                <span className="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                  {alerts.length}
                </span>
              )}
            </h3>
            <p className="text-xs lg:text-sm text-gray-600 mt-1">
              Items predicted to fall below reorder levels in the next 14 days
            </p>
          </div>

          {alerts.length === 0 ? (
            <div className="p-8 lg:p-12 text-center">
              <CheckCircle className="h-10 lg:h-12 w-10 lg:w-12 mx-auto text-green-500 mb-3" />
              <h4 className="text-base lg:text-lg font-medium text-gray-900 mb-2">No Critical Alerts</h4>
              <p className="text-sm text-gray-600">All inventory levels are within acceptable ranges</p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Priority
                    </th>
                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Warehouse
                    </th>
                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Category
                    </th>
                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Stock Level
                    </th>
                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Days Left
                    </th>
                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Stockout Date
                    </th>
                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Daily Demand
                    </th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {alerts.slice(0, 10).map((alert, index) => (
                    <tr key={index} className={`hover:bg-gray-50 border-l-4 ${getSeverityColor(alert.severity)}`}>
                      <td className="px-4 lg:px-6 py-3 whitespace-nowrap">
                        <span className={`px-2 py-1 text-xs font-semibold rounded-full ${getSeverityBadge(alert.severity)}`}>
                          {alert.severity.toUpperCase()}
                        </span>
                      </td>
                      <td className="px-4 lg:px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                        {alert.warehouse_id}
                      </td>
                      <td className="px-4 lg:px-6 py-3 whitespace-nowrap text-sm text-gray-900">
                        {alert.product_category}
                      </td>
                      <td className="px-4 lg:px-6 py-3 whitespace-nowrap text-sm">
                        <div className="text-gray-900 font-medium">{alert.current_stock.toFixed(0)}</div>
                        <div className="text-xs text-gray-500">Reorder: {alert.reorder_level.toFixed(0)}</div>
                      </td>
                      <td className="px-4 lg:px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                        {alert.days_until_stockout} days
                      </td>
                      <td className="px-4 lg:px-6 py-3 whitespace-nowrap text-sm text-gray-600">
                        {new Date(alert.predicted_stockout_date).toLocaleDateString()}
                      </td>
                      <td className="px-4 lg:px-6 py-3 whitespace-nowrap text-sm text-gray-600">
                        {alert.predicted_daily_demand.toFixed(1)} units
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
          
          {alerts.length > 10 && (
            <div className="px-4 lg:px-6 py-3 bg-gray-50 border-t border-gray-200 text-center">
              <p className="text-sm text-gray-600">
                Showing top 10 alerts. {alerts.length - 10} more alerts available.
              </p>
            </div>
          )}
        </div>
      )}

      {/* Analytics Unavailable Message */}
      {!loadingAnalytics && !analyticsAvailable && (
        <div className="bg-white rounded-lg shadow-sm border-2 border-yellow-200 p-8 lg:p-12">
          <div className="text-center max-w-2xl mx-auto">
            <XCircle className="h-12 lg:h-16 w-12 lg:w-16 mx-auto text-yellow-500 mb-4" />
            <h3 className="text-lg lg:text-2xl font-bold text-gray-900 mb-2">ML Analytics Unavailable</h3>
            <p className="text-sm lg:text-base text-gray-600 mb-6">
              The ML analytics service is currently not running. Advanced features like demand forecasting 
              and AI-powered reorder alerts are temporarily unavailable.
            </p>
            <div className="bg-gray-50 rounded-lg p-4 border border-gray-200">
              <p className="text-sm text-gray-700 font-medium mb-2">To enable ML analytics:</p>
              <code className="block text-xs bg-gray-900 text-green-400 p-3 rounded font-mono">
                cd /opt/lampp/htdocs/PIMS/fastapi<br/>
                python main.py
              </code>
            </div>
          </div>
        </div>
      )}

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
