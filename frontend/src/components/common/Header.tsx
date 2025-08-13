import { Bell, Search, User, LogOut, Settings, Menu } from 'lucide-react';

interface HeaderProps {
  onMenuClick?: () => void;
}

export function Header({ onMenuClick }: HeaderProps) {
  return (
    <header className="bg-white border-b border-gray-200 px-4 lg:px-6 py-4">
      <div className="flex items-center justify-between">
        {/* Mobile menu button and Search */}
        <div className="flex items-center space-x-4 flex-1">
          {onMenuClick && (
            <button
              onClick={onMenuClick}
              className="lg:hidden p-2 text-gray-400 hover:text-gray-600 transition-colors"
            >
              <Menu className="h-6 w-6" />
            </button>
          )}
          
          {/* Search - hidden on small screens, visible on medium+ */}
          <div className="hidden md:block flex-1 max-w-lg">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
              <input
                type="text"
                placeholder="Search..."
                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </div>
        </div>

        {/* Right side */}
        <div className="flex items-center space-x-2 lg:space-x-4">
          {/* Mobile search button - visible on small screens only */}
          <button className="md:hidden p-2 text-gray-400 hover:text-gray-600 transition-colors">
            <Search className="h-5 w-5" />
          </button>

          {/* Notifications */}
          <button className="relative p-2 text-gray-400 hover:text-gray-600 transition-colors">
            <Bell className="h-5 w-5" />
            <span className="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
              3
            </span>
          </button>

          {/* User menu */}
          <div className="relative group">
            <button className="flex items-center space-x-2 lg:space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-colors">
              <div className="hidden lg:block text-right">
                <div className="text-sm font-medium text-gray-900">Demo User</div>
                <div className="text-xs text-gray-500">Demo Company</div>
              </div>
              <div className="h-8 w-8 bg-blue-500 rounded-full flex items-center justify-center">
                <User className="h-4 w-4 text-white" />
              </div>
            </button>

            {/* Dropdown menu */}
            <div className="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10">
              <div className="py-1">
                <a href="#" className="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <Settings className="h-4 w-4 mr-3" />
                  Settings
                </a>
                <button className="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <LogOut className="h-4 w-4 mr-3" />
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>
  );
}
