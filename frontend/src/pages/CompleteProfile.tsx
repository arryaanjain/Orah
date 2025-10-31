import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { api } from '../services/api';

interface CompleteProfileResponse {
  message: string;
  user: any;
  company: any;
}

export function CompleteProfile() {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    company_name: '',
    company_email: '',
    gst_number: '',
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      const response = await api.post<CompleteProfileResponse>('/auth/complete-profile', formData);

      // Update stored user data
      localStorage.setItem('user', JSON.stringify(response.data.user));
      
      // Navigate to dashboard
      navigate('/');
    } catch (err: any) {
      console.error('Complete profile error:', err);
      setError(err.response?.data?.message || err.message || 'Failed to complete profile');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 p-4">
      <div className="bg-white rounded-2xl shadow-xl p-8 max-w-md w-full">
        <div className="text-center mb-8">
          <h2 className="text-3xl font-bold text-gray-900">Complete Your Profile</h2>
          <p className="text-gray-600 mt-2">Tell us about your company</p>
        </div>

        {error && (
          <div className="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Company Name */}
          <div>
            <label htmlFor="company_name" className="block text-sm font-medium text-gray-700 mb-2">
              Company Name *
            </label>
            <input
              type="text"
              id="company_name"
              required
              value={formData.company_name}
              onChange={(e) => setFormData({ ...formData, company_name: e.target.value })}
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Enter your company name"
            />
          </div>

          {/* Company Email */}
          <div>
            <label htmlFor="company_email" className="block text-sm font-medium text-gray-700 mb-2">
              Company Email *
            </label>
            <input
              type="email"
              id="company_email"
              required
              value={formData.company_email}
              onChange={(e) => setFormData({ ...formData, company_email: e.target.value })}
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="company@example.com"
            />
          </div>

          {/* GST Number */}
          <div>
            <label htmlFor="gst_number" className="block text-sm font-medium text-gray-700 mb-2">
              GST Number (Optional)
            </label>
            <input
              type="text"
              id="gst_number"
              value={formData.gst_number}
              onChange={(e) => setFormData({ ...formData, gst_number: e.target.value })}
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Enter GST number if applicable"
            />
          </div>

          {/* Submit Button */}
          <button
            type="submit"
            disabled={loading}
            className="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {loading ? 'Creating Company...' : 'Complete Profile'}
          </button>
        </form>

        <div className="mt-6 text-center text-sm text-gray-600">
          <p>This information will be used to set up your company account</p>
        </div>
      </div>
    </div>
  );
}
