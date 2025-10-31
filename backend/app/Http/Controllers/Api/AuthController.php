<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            // Check if user exists with this Google ID
            $user = User::where('google_id', $googleUser->id)->first();
            
            if (!$user) {
                // Check if user exists with this email
                $user = User::where('email', $googleUser->email)->first();
                
                if (!$user) {
                    // Create new user without company (profile not completed)
                    $user = User::create([
                        'google_id' => $googleUser->id,
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'avatar' => $googleUser->avatar,
                        'profile_completed' => false,
                        'email_verified_at' => now(),
                    ]);
                } else {
                    // Update existing user with Google ID
                    $user->update([
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar,
                    ]);
                }
            }
            
            // Create token for the user
            $token = $user->createToken('auth-token')->plainTextToken;
            
            // Prepare data for frontend
            $userData = [
                'user' => $user,
                'token' => $token,
                'profile_completed' => $user->profile_completed,
                'has_company' => $user->company_id !== null,
            ];
            
            // Encode data for URL
            $encodedData = base64_encode(json_encode($userData));
            
            // Redirect to frontend callback with data
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return redirect()->to($frontendUrl . '/auth/callback?data=' . $encodedData);
            
        } catch (\Exception $e) {
            // Redirect to frontend with error
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return redirect()->to($frontendUrl . '/login?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Complete user profile with company details
     */
    public function completeProfile(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'gst_number' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        // Create company
        $company = Company::create([
            'name' => $request->company_name,
            'email' => $request->company_email,
            'gst_number' => $request->gst_number,
        ]);

        // Update user with company and mark profile as completed
        $user->update([
            'company_id' => $company->id,
            'profile_completed' => true,
        ]);

        return response()->json([
            'message' => 'Profile completed successfully',
            'user' => $user->load('company'),
            'company' => $company,
        ]);
    }

    /**
     * Link user to existing company
     */
    public function linkToCompany(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
        ]);

        $user = $request->user();

        // Update user with company
        $user->update([
            'company_id' => $request->company_id,
            'profile_completed' => true,
        ]);

        return response()->json([
            'message' => 'Linked to company successfully',
            'user' => $user->load('company'),
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('company'),
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Find companies by email domain
     */
    public function findCompaniesByEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;
        $domain = substr(strrchr($email, "@"), 1);

        // Find companies with matching email domain
        $companies = Company::where('email', 'LIKE', "%@{$domain}")
            ->get(['id', 'name', 'email']);

        return response()->json([
            'companies' => $companies,
            'has_companies' => $companies->count() > 0,
        ]);
    }
}
