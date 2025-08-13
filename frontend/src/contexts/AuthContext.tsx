import { createContext, useContext, useEffect, useState } from 'react';
import type { ReactNode } from 'react';
import type { User, Company } from '../types';
import { authService } from '../services';
import toast from 'react-hot-toast';

interface AuthContextType {
  user: User | null;
  company: Company | null;
  token: string | null;
  loading: boolean;
  login: (credentials: { company_name: string; email: string; password: string }) => Promise<boolean>;
  register: (data: { 
    company_name: string; 
    name: string; 
    email: string; 
    password: string; 
    password_confirmation: string;
  }) => Promise<boolean>;
  logout: () => void;
  isAuthenticated: boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [company, setCompany] = useState<Company | null>(null);
  const [token, setToken] = useState<string | null>(localStorage.getItem('auth_token'));
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    initializeAuth();
  }, []);

  const initializeAuth = async () => {
    if (token) {
      try {
        const response = await authService.me();
        if (response.success && response.data) {
          setUser(response.data.user);
          setCompany(response.data.company);
        } else {
          clearAuth();
        }
      } catch (error) {
        console.error('Auth initialization error:', error);
        clearAuth();
      }
    }
    setLoading(false);
  };

  const login = async (credentials: { company_name: string; email: string; password: string }): Promise<boolean> => {
    try {
      const response = await authService.login(credentials);
      if (response.success && response.data) {
        const { user, token, company } = response.data;
        setUser(user);
        setCompany(company);
        setToken(token);
        localStorage.setItem('auth_token', token);
        localStorage.setItem('user', JSON.stringify(user));
        localStorage.setItem('company', JSON.stringify(company));
        toast.success('Welcome back!');
        return true;
      } else {
        toast.error(response.message || 'Login failed');
        return false;
      }
    } catch (error: any) {
      console.error('Login error:', error);
      toast.error(error.response?.data?.message || 'Login failed');
      return false;
    }
  };

  const register = async (data: { 
    company_name: string; 
    name: string; 
    email: string; 
    password: string; 
    password_confirmation: string;
  }): Promise<boolean> => {
    try {
      const response = await authService.register(data);
      if (response.success && response.data) {
        const { user, token, company } = response.data;
        setUser(user);
        setCompany(company);
        setToken(token);
        localStorage.setItem('auth_token', token);
        localStorage.setItem('user', JSON.stringify(user));
        localStorage.setItem('company', JSON.stringify(company));
        toast.success('Account created successfully!');
        return true;
      } else {
        toast.error(response.message || 'Registration failed');
        return false;
      }
    } catch (error: any) {
      console.error('Registration error:', error);
      toast.error(error.response?.data?.message || 'Registration failed');
      return false;
    }
  };

  const logout = () => {
    clearAuth();
    toast.success('Logged out successfully');
  };

  const clearAuth = () => {
    setUser(null);
    setCompany(null);
    setToken(null);
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    localStorage.removeItem('company');
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        company,
        token,
        loading,
        login,
        register,
        logout,
        isAuthenticated: !!user && !!token,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
