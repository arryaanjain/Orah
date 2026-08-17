import { useState, useEffect, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Edit2, Trash2, Save, X, Users, Mail, Phone, MapPin, Building } from 'lucide-react';
import { customerService } from '../services';
import toast from 'react-hot-toast';
import type { Customer } from '../types';

interface CustomerFormData {
  billing_name: string;
  name: string;
  email: string;
  phone: string;
  place: string;
  gst_number: string;
  address: string;
}

export function Customers() {
  const navigate = useNavigate();
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [editingCustomer, setEditingCustomer] = useState<Customer | null>(null);
  const [searchTerm, setSearchTerm] = useState('');

  const [formData, setFormData] = useState<CustomerFormData>({
    billing_name: '',
    name: '',
    email: '',
    phone: '',
    place: '',
    gst_number: '',
    address: '',
  });

  const loadCustomers = useCallback(async () => {
    try {
      setLoading(true);
      const response = await customerService.getAll();
      setCustomers(response.customers || []);
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to load customers');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    loadCustomers();
  }, [loadCustomers]);

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await customerService.create({
        billing_name: formData.billing_name || formData.name,
        name: formData.name || formData.billing_name,
        email: formData.email || undefined,
        phone: formData.phone || undefined,
        place: formData.place || undefined,
        gst_number: formData.gst_number || undefined,
        address: formData.address || undefined,
      });
      toast.success('Customer created successfully');
      setShowForm(false);
      resetForm();
      loadCustomers();
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to create customer');
    }
  };

  const handleUpdate = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingCustomer) return;

    try {
      await customerService.update(editingCustomer.id, {
        billing_name: formData.billing_name || formData.name,
        name: formData.name || formData.billing_name,
        email: formData.email || undefined,
        phone: formData.phone || undefined,
        place: formData.place || undefined,
        gst_number: formData.gst_number || undefined,
        address: formData.address || undefined,
      });
      toast.success('Customer updated successfully');
      setEditingCustomer(null);
      resetForm();
      loadCustomers();
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to update customer');
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Are you sure you want to delete this customer?')) return;

    try {
      await customerService.delete(id);
      toast.success('Customer deleted successfully');
      loadCustomers();
    } catch (error: any) {
      toast.error(error.response?.data?.message || 'Failed to delete customer');
    }
  };

  const handleEdit = (customer: Customer) => {
    setEditingCustomer(customer);
    setFormData({
      billing_name: customer.billing_name || customer.name || '',
      name: customer.name || customer.billing_name || '',
      email: customer.email || '',
      phone: customer.phone || '',
      place: customer.place || '',
      gst_number: customer.gst_number || '',
      address: customer.address || '',
    });
  };

  const resetForm = () => {
    setFormData({
      billing_name: '',
      name: '',
      email: '',
      phone: '',
      place: '',
      gst_number: '',
      address: '',
    });
  };

  const filteredCustomers = customers.filter((customer) => {
    const search = searchTerm.toLowerCase();
    const billing = (customer.billing_name || '').toLowerCase();
    const name = (customer.name || '').toLowerCase();
    const email = (customer.email || '').toLowerCase();
    const phone = customer.phone || '';
    const gst = (customer.gst_number || '').toLowerCase();

    return billing.includes(search) || name.includes(search) || email.includes(search) || phone.includes(search) || gst.includes(search);
  });

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin h-12 w-12 border-4 border-blue-500 border-t-transparent rounded-full"></div>
      </div>
    );
  }

  return (
    <div className="p-4 lg:p-6 space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Customer Management</h1>
          <p className="text-sm text-gray-600 mt-1">Manage billing names, GST numbers, and customer contacts</p>
        </div>
        <div className="flex gap-3">
          <button
            onClick={() => {
              resetForm();
              setShowForm(true);
            }}
            className="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm"
          >
            <Plus className="h-5 w-5" />
            Add Customer
          </button>
          <button
            onClick={() => navigate('/')}
            className="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors text-sm"
          >
            Back to Dashboard
          </button>
        </div>
      </div>

      {/* Search Bar */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <input
          type="text"
          placeholder="Search customers by billing name, contact, email, GST #, or phone..."
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
          className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm focus:border-transparent"
        />
      </div>

      {/* Customers List */}
      {filteredCustomers.length === 0 ? (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
          <Users className="h-16 w-16 mx-auto text-gray-400 mb-4" />
          <h3 className="text-lg font-semibold text-gray-900 mb-2">
            {searchTerm ? 'No customers found' : 'No Customers Registered Yet'}
          </h3>
          <p className="text-gray-600 mb-4">
            {searchTerm ? 'Try adjusting your search criteria' : 'Get started by creating your customer database'}
          </p>
          {!searchTerm && (
            <button
              onClick={() => {
                resetForm();
                setShowForm(true);
              }}
              className="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors text-sm"
            >
              <Plus className="h-5 w-5" />
              Add First Customer
            </button>
          )}
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {filteredCustomers.map((customer) => (
            <div key={customer.id} className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow flex flex-col justify-between">
              <div>
                <div className="flex justify-between items-start mb-3">
                  <div className="flex-1">
                    <h3 className="text-lg font-bold text-gray-900">{customer.billing_name || customer.name}</h3>
                    {customer.name && customer.name !== customer.billing_name && (
                      <p className="text-xs text-gray-500">Contact Person: {customer.name}</p>
                    )}
                  </div>
                  <div className="flex gap-1 ml-2">
                    <button
                      onClick={() => handleEdit(customer)}
                      className="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition-colors"
                    >
                      <Edit2 className="h-4 w-4" />
                    </button>
                    <button
                      onClick={() => handleDelete(customer.id)}
                      className="p-1.5 text-red-600 hover:bg-red-50 rounded transition-colors"
                    >
                      <Trash2 className="h-4 w-4" />
                    </button>
                  </div>
                </div>

                <div className="space-y-1.5 text-sm text-gray-600 mb-4">
                  {customer.gst_number && (
                    <div className="flex items-center gap-2 text-xs font-semibold text-purple-700 bg-purple-50 p-1.5 rounded">
                      <Building className="h-3.5 w-3.5" />
                      <span>GST: {customer.gst_number}</span>
                    </div>
                  )}
                  {customer.email && (
                    <div className="flex items-center gap-2 text-xs">
                      <Mail className="h-3.5 w-3.5 text-gray-400" />
                      <span className="truncate">{customer.email}</span>
                    </div>
                  )}
                  {customer.phone && (
                    <div className="flex items-center gap-2 text-xs">
                      <Phone className="h-3.5 w-3.5 text-gray-400" />
                      <span>{customer.phone}</span>
                    </div>
                  )}
                  {customer.place && (
                    <div className="flex items-center gap-2 text-xs text-gray-500">
                      <MapPin className="h-3.5 w-3.5 text-gray-400" />
                      <span>{customer.place}</span>
                    </div>
                  )}
                  {customer.address && (
                    <div className="text-xs text-gray-500 pt-1 border-t border-gray-100">
                      {customer.address}
                    </div>
                  )}
                </div>
              </div>

              <div className="text-[11px] text-gray-400 pt-2 border-t border-gray-100">
                Created: {new Date(customer.created_at).toLocaleDateString()}
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Customer Form Modal */}
      {(showForm || editingCustomer) && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-lg shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center mb-4">
              <h2 className="text-xl font-bold text-gray-900">
                {editingCustomer ? 'Edit Customer' : 'Add New Customer'}
              </h2>
              <button
                onClick={() => {
                  setShowForm(false);
                  setEditingCustomer(null);
                  resetForm();
                }}
                className="text-gray-400 hover:text-gray-600"
              >
                <X className="h-6 w-6" />
              </button>
            </div>

            <form onSubmit={editingCustomer ? handleUpdate : handleCreate} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Billing Name (Business / Firm Name) *
                </label>
                <input
                  type="text"
                  required
                  value={formData.billing_name}
                  onChange={(e) => setFormData({ ...formData, billing_name: e.target.value, name: formData.name || e.target.value })}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                  placeholder="e.g., Apex Retail Corp"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Contact Person Name
                </label>
                <input
                  type="text"
                  value={formData.name}
                  onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                  placeholder="e.g., Rajesh Sharma"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    GST Number
                  </label>
                  <input
                    type="text"
                    value={formData.gst_number}
                    onChange={(e) => setFormData({ ...formData, gst_number: e.target.value.toUpperCase() })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm font-mono"
                    placeholder="22AAAAA0000A1Z5"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Place / City
                  </label>
                  <input
                    type="text"
                    value={formData.place}
                    onChange={(e) => setFormData({ ...formData, place: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                    placeholder="e.g., Mumbai"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Email Address
                  </label>
                  <input
                    type="email"
                    value={formData.email}
                    onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                    placeholder="billing@apex.com"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Phone Number
                  </label>
                  <input
                    type="tel"
                    value={formData.phone}
                    onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                    placeholder="+91 9876543210"
                  />
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Full Address
                </label>
                <textarea
                  rows={2}
                  value={formData.address}
                  onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"
                  placeholder="Street address..."
                />
              </div>

              <div className="flex gap-3 pt-4 border-t border-gray-200">
                <button
                  type="submit"
                  className="flex-1 flex items-center justify-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm"
                >
                  <Save className="h-4 w-4" />
                  {editingCustomer ? 'Update Customer' : 'Create Customer'}
                </button>
                <button
                  type="button"
                  onClick={() => {
                    setShowForm(false);
                    setEditingCustomer(null);
                    resetForm();
                  }}
                  className="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors text-sm"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
