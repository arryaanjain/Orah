import { NavLink } from 'react-router-dom';
import { 
  Home, 
  Package, 
  ShoppingCart, 
  Truck, 
  ClipboardList, 
  BarChart3,
  Archive,
  X
} from 'lucide-react';

const navigation = [
  { name: 'Home', href: '/', icon: Home },
  { name: 'Add New Product', href: '/products', icon: Package },
  { name: 'Order Book', href: '/orders', icon: ClipboardList },
  { name: 'Sales Book', href: '/sales', icon: Truck },
  { name: 'Raw Material Purchase', href: '/purchases', icon: ShoppingCart },
  { name: 'Raw Material Master', href: '/raw-materials', icon: Archive },
  { name: 'Records', href: '/reports', icon: BarChart3 },
];

interface SidebarProps {
  onClose?: () => void;
}

export function Sidebar({ onClose }: SidebarProps) {
  return (
    <div className="w-64 bg-gray-900 h-full flex flex-col">
      {/* Header with mobile close button */}
      <div className="p-6 flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-white">Orah</h1>
          <p className="text-sm text-gray-300 mt-1">Inventory Management</p>
        </div>
        {onClose && (
          <button
            onClick={onClose}
            className="lg:hidden text-gray-400 hover:text-white p-1"
          >
            <X className="h-6 w-6" />
          </button>
        )}
      </div>

      {/* Navigation */}
      <nav className="flex-1 px-4 space-y-1">
        {navigation.map((item) => (
          <NavLink
            key={item.name}
            to={item.href}
            onClick={onClose} // Close mobile menu on navigation
            className={({ isActive }) =>
              `group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors ${
                isActive
                  ? 'bg-blue-600 text-white'
                  : 'text-gray-300 hover:bg-gray-800 hover:text-white'
              }`
            }
          >
            <item.icon className="mr-3 h-5 w-5 flex-shrink-0" />
            <span className="truncate">{item.name}</span>
          </NavLink>
        ))}
      </nav>

      {/* Footer */}
      <div className="p-4 border-t border-gray-800">
        <div className="text-xs text-gray-400">
          Version 2.0
        </div>
      </div>
    </div>
  );
}
