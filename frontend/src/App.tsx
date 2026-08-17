import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { Toaster } from 'react-hot-toast';
import { Layout } from './components/layout';
import { 
  Dashboard, 
  Login, 
  AuthCallback, 
  CompleteProfile, 
  RawMaterialMaster, 
  Customers, 
  RawMaterialPurchase, 
  ProductManagement,
  OrderBook,
  SalesBook
} from './pages';

// Protected Route Component
function ProtectedRoute({ children }: { children: React.ReactNode }) {
  const token = localStorage.getItem('token');
  const user = localStorage.getItem('user');
  
  if (!token) {
    return <Navigate to="/login" replace />;
  }

  if (user) {
    try {
      const userData = JSON.parse(user);
      if (!userData.profile_completed) {
        return <Navigate to="/complete-profile" replace />;
      }
    } catch {
      // JSON parse error, proceed
    }
  }

  return <>{children}</>;
}

function Records() {
  return (
    <div className="p-6">
      <h1 className="text-2xl font-bold text-gray-900 mb-4">Records</h1>
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <p className="text-gray-600">Records and reports system initialized.</p>
      </div>
    </div>
  );
}

function App() {
  return (
    <Router>
      <div className="h-screen overflow-hidden">
        <Routes>
          {/* Public Routes */}
          <Route path="/login" element={<Login />} />
          <Route path="/auth/callback" element={<AuthCallback />} />
          <Route path="/complete-profile" element={<CompleteProfile />} />

          {/* Protected Routes */}
          <Route path="/" element={<ProtectedRoute><Layout /></ProtectedRoute>}>
            <Route index element={<Dashboard />} />
            <Route path="raw-materials" element={<RawMaterialMaster />} />
            <Route path="purchases" element={<RawMaterialPurchase />} />
            <Route path="products" element={<ProductManagement />} />
            <Route path="orders" element={<OrderBook />} />
            <Route path="sales" element={<SalesBook />} />
            <Route path="customers" element={<Customers />} />
            <Route path="production" element={<div className="p-6"><h1 className="text-2xl font-bold">Production</h1></div>} />
            <Route path="reports" element={<Records />} />
            <Route path="settings" element={<div className="p-6"><h1 className="text-2xl font-bold">Settings</h1></div>} />
          </Route>
        </Routes>
        <Toaster 
          position="top-right"
          toastOptions={{
            duration: 4000,
            style: {
              background: '#363636',
              color: '#fff',
            },
          }}
        />
      </div>
    </Router>
  );
}

export default App;
