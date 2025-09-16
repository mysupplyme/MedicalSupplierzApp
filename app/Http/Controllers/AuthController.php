<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $client = Client::where('email', $request->email)
                       ->where('type', 'buyer')
                       ->first();

        if (!$client) {
            return $this->error('Doctor not found', 401);
        }

        if (!Hash::check($request->password, $client->password)) {
            return $this->error('Invalid credentials', 401);
        }

        return $this->success([
            'user' => $client,
            'token' => 'simple-token-' . $client->id
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:clients',
            'password' => 'required|string|min:6',
            'mobile_number' => 'required|string|max:20',
            'country_code' => 'required|exists:countries,id',
            'job_title' => 'nullable|string|max:150',
            'workplace' => 'nullable|string',
            'specialty_id' => 'required|exists:categories,id',
            'sub_specialty_id' => 'nullable|exists:categories,id',
            'nationality' => 'nullable|exists:countries,id',
            'residency' => 'nullable|exists:countries,id',
        ]);

        $firstName = $request->first_name;
        $lastName = $request->last_name;
        
        // Get country phone prefix
        $country = Country::find($request->country_code);
        $phonePrefix = $country ? $country->phone_prefix : '';
        $fullMobileNumber = '+' . ltrim($phonePrefix, '+') . $request->mobile_number;
        
        $client = Client::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'type' => 'buyer',
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'mobile_number' => $fullMobileNumber,
            'country_code' => $request->country_code,
            'job_title' => $request->job_title,
            'workplace' => $request->workplace,
            'specialty_id' => $request->specialty_id,
            'sub_specialty_id' => $request->sub_specialty_id,
            'nationality' => $request->nationality,
            'residency' => $request->residency,
            'buyer_type' => 'doctor',
            'is_buyer' => 1,
            'status' => 1,
        ]);

        return $this->success($client, 'Doctor registered successfully');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $client = Client::where('email', $request->email)
                       ->where('type', 'buyer')
                       ->first();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Email not found'
            ], 404);
        }

        // Generate reset token
        $resetToken = \Illuminate\Support\Str::random(60);
        $client->update([
            'reset_token' => $resetToken,
            'reset_expired_at' => now()->addHours(1)
        ]);

        // Send password reset email
        $resetUrl = url('/reset-password?token=' . $resetToken . '&email=' . $client->email);
        
        try {
            Mail::send('emails.password-reset', [
                'client' => $client,
                'resetUrl' => $resetUrl
            ], function ($message) use ($client) {
                $message->to($client->email)
                        ->subject('Password Reset Request - MedicalSupplierz');
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Reset instructions sent to your email'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Email error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function getProfile(Request $request)
    {
        $client = $request->get('auth_user');
        $client->load('countryCode:id,currency_id');
        
        return response()->json([
            'success' => true,
            'data' => $client
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'string|max:255',
            'mobile_number' => 'string|max:20',
            'country_code' => 'exists:countries,id',
            'job_title' => 'nullable|string|max:150',
            'workplace' => 'nullable|string',
            'specialty_id' => 'exists:categories,id',
            'sub_specialty_id' => 'nullable|exists:categories,id',
            'residency' => 'exists:countries,id',
            'nationality' => 'exists:countries,id',
        ]);

        $client = $request->get('auth_user');
        
        $updateData = $request->only([
            'mobile_number', 'country_code', 'workplace',
            'specialty_id', 'sub_specialty_id', 'residency', 'nationality'
        ]);
        
        if ($request->has('job_title')) {
            $updateData['job_title'] = $request->job_title;
        }
        
        // Handle name field - split into first_name and last_name
        if ($request->has('name')) {
            $nameParts = explode(' ', $request->name, 2);
            $updateData['first_name'] = $nameParts[0];
            $updateData['last_name'] = isset($nameParts[1]) ? $nameParts[1] : '';
        }
        
        $client->update($updateData);
        $client->refresh();

        return response()->json([
            'success' => true,
            'data' => $client
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6',
        ]);

        $client = $request->get('auth_user');

        if (!Hash::check($request->current_password, $client->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $client->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }
    
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // Debug: Check if client exists with email
        $client = Client::where('email', $request->email)->first();
        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Email not found'
            ], 400);
        }

        // Debug: Check token and expiry separately
        if ($client->reset_token !== $request->token) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reset token'
            ], 400);
        }

        if (!$client->reset_expired_at || $client->reset_expired_at <= now()) {
            return response()->json([
                'success' => false,
                'message' => 'Reset token has expired'
            ], 400);
        }

        $client->update([
            'password' => Hash::make($request->password),
            'reset_token' => null,
            'reset_expired_at' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. You can now login with your new password.'
        ]);
    }
}