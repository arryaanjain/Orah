import { useEffect, useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { api } from '../services/api';

interface Company {
  id: number;
  name: string;
  email: string;
}

interface CompaniesResponse {
  companies: Company[];
  has_companies: boolean;
}

interface LinkCompanyResponse {
  message: string;
  user: any;
}

export function AuthCallback() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [companies, setCompanies] = useState<Company[]>([]);
  const [userEmail, setUserEmail] = useState('');
  const [error, setError] = useState('');

  useEffect(() => {
    handleOAuthCallback();
  }, []);

  const handleOAuthCallback = async () => {
    try {
      // Get the encoded data from URL params
      const encodedData = searchParams.get('data');
      
      if (!encodedData) {
        throw new Error('No authentication data received');
      }

      // Decode the data
      const decodedData = JSON.parse(atob(encodedData));
      const { user, token, profile_completed, has_company } = decodedData;

      // Store token
      localStorage.setItem('token', token);
      
      // Store user data
      localStorage.setItem('user', JSON.stringify(user));
      
      setUserEmail(user.email);

      // Check if profile is completed
      if (profile_completed && has_company) {
        // User has completed profile and linked to company
        navigate('/');
      } else {
        // Profile not completed, check for existing companies
        try {
          const companiesResponse = await api.post<CompaniesResponse>('/auth/find-companies', {
            email: user.email,
          });

          if (companiesResponse.data.has_companies && companiesResponse.data.companies.length > 0) {
            // Show companies to link to
            setCompanies(companiesResponse.data.companies);
            setLoading(false);
          } else {
            // No companies found, redirect to complete profile
            navigate('/complete-profile');
          }
        } catch (err) {
          console.error('Error fetching companies:', err);
          // If error fetching companies, just go to complete profile
          navigate('/complete-profile');
        }
      }
    } catch (err: any) {
      console.error('OAuth callback error:', err);
      setError(err.message || 'Authentication failed');
      setLoading(false);
      
      // Redirect to login after 3 seconds
      setTimeout(() => {
        navigate('/login');
      }, 3000);
    }
  };

  const handleContinueAsCompany = async (companyId: number) => {
    try {
      const response = await api.post<LinkCompanyResponse>('/auth/link-company', { company_id: companyId });

      // Update stored user data
      localStorage.setItem('user', JSON.stringify(response.data.user));
      
      // Navigate to dashboard
      navigate('/');
    } catch (err: any) {
      console.error('Link company error:', err);
      setError(err.response?.data?.message || err.message || 'Failed to link company');
    }
  };

  const handleCreateNewCompany = () => {
    navigate('/complete-profile');
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="text-center">
          <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
          <p className="mt-4 text-gray-600">Authenticating...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
          <div className="text-center">
            <div className="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
              <svg className="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </div>
            <h3 className="mt-4 text-lg font-medium text-gray-900">Authentication Failed</h3>
            <p className="mt-2 text-sm text-gray-600">{error}</p>
            <p className="mt-4 text-xs text-gray-500">Redirecting to login...</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 p-4">
      <div className="bg-white rounded-2xl shadow-xl p-8 max-w-2xl w-full">
        <div className="text-center mb-8">
          <h2 className="text-3xl font-bold text-gray-900">Welcome!</h2>
          <p className="text-gray-600 mt-2">We found existing companies associated with {userEmail}</p>
        </div>

        <div className="space-y-4 mb-6">
          <h3 className="text-lg font-semibold text-gray-800">Continue as:</h3>
          
          {companies.map((company) => (
            <button
              key={company.id}
              onClick={() => handleContinueAsCompany(company.id)}
              className="w-full flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all duration-200"
            >
              <div className="text-left">
                <p className="font-semibold text-gray-900">{company.name}</p>
                <p className="text-sm text-gray-600">{company.email}</p>
              </div>
              <svg className="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            </button>
          ))}
        </div>

        <div className="relative my-8">
          <div className="absolute inset-0 flex items-center">
            <div className="w-full border-t border-gray-300"></div>
          </div>
          <div className="relative flex justify-center text-sm">
            <span className="px-4 bg-white text-gray-500">Or</span>
          </div>
        </div>

        <button
          onClick={handleCreateNewCompany}
          className="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors font-medium"
        >
          Create New Company
        </button>
      </div>
    </div>
  );
}
