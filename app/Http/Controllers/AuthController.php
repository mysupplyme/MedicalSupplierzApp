<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\ClientBusinessInfo;
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

        if (!$client->isEmailVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Please activate your account first. Check your email for activation code.',
                'requires_activation' => true
            ], 403);
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
            'country_id' => 'required|exists:countries,id',
            'country_code' => 'required|string|max:5',
            'phone_prefix' => 'required|string|max:10',
            'job_title' => 'nullable|string|max:150',
            'workplace' => 'nullable|string',
            'specialty_id' => 'required|exists:categories,id',
            'sub_specialty_id' => 'nullable|exists:categories,id',
            'nationality' => 'nullable|exists:countries,id',
            'residency' => 'nullable|exists:countries,id',
        ]);

        $firstName = $request->first_name;
        $lastName = $request->last_name;
        
        // Use provided parameters directly from request
        $fullMobileNumber = $request->phone_prefix . $request->mobile_number;
        
        $uuid = \Illuminate\Support\Str::uuid();
        
        $client = Client::create([
            'uuid' => $uuid,
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
        
        // Create business info record immediately
        ClientBusinessInfo::create([
            'client_id' => $client->id,
            'uuid' => $uuid,
            'business_type' => $request->business_type ?? 'subscription',
            'reg_number' => null
        ]);

        // Generate activation code and send activation email
        $client->email_activation_code = random_int(100000, 999999);
        $client->save();
        
        try {
            Mail::send('emails.activation', [
                'client' => $client,
                'activation_code' => $client->email_activation_code
            ], function ($message) use ($client) {
                $message->to($client->email)
                        ->subject('Activate Your Medical Supplierz Account');
            });
        } catch (\Exception $e) {
            \Log::error('Activation email failed: ' . $e->getMessage());
        }

        // Add country details to response for debugging
        $response = $client->toArray();
        $response['country_id'] = $request->country_id;
        $response['country_code'] = $request->country_code;
        $response['phone_prefix'] = $request->phone_prefix;
        
        return $this->success($response, 'Doctor registered successfully');
    }

    public function activateAccount(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'activation_code' => 'required|string|size:6'
        ]);

        $client = Client::where('email', $request->email)
                       ->where('email_activation_code', $request->activation_code)
                       ->where('type', 'buyer')
                       ->first();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid activation code or email'
            ], 400);
        }

        if ($client->isEmailVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Account is already activated'
            ], 400);
        }

        $client->markEmailAsVerified();

        // Send welcome email after activation
        try {
            Mail::send('emails.welcome', ['client' => $client], function ($message) use ($client) {
                $message->to($client->email)
                        ->subject('Welcome to Medical Supplierz - Account Activated');
            });
        } catch (\Exception $e) {
            \Log::error('Welcome email failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Account activated successfully! You can now login.',
            'data' => [
                'email_verified' => true,
                'verified_at' => $client->email_verified_at
            ]
        ]);
    }

    public function resendActivation(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $client = Client::where('email', $request->email)
                       ->where('type', 'buyer')
                       ->first();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Email not found'
            ], 404);
        }

        if ($client->isEmailVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Account is already activated'
            ], 400);
        }

        // Generate new activation code
        $client->email_activation_code = random_int(100000, 999999);
        $client->save();

        try {
            Mail::send('emails.activation', [
                'client' => $client,
                'activation_code' => $client->email_activation_code
            ], function ($message) use ($client) {
                $message->to($client->email)
                        ->subject('Activate Your Medical Supplierz Account');
            });

            return response()->json([
                'success' => true,
                'message' => 'Activation code sent to your email'
            ]);
        } catch (\Exception $e) {
            \Log::error('Resend activation email failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send activation email'
            ], 500);
        }
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

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $client = Client::where('email', $request->email)->first();
        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Email not found'
            ], 400);
        }

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
        $client->load(['clientSetting']);
        
        $response = $client->toArray();
        
        $currencyId = null;
        if ($client->clientSetting && $client->clientSetting->currency_id) {
            $currencyId = $client->clientSetting->currency_id;
        } elseif ($client->residency) {
            $residencyCountry = Country::find($client->residency);
            $currencyId = $residencyCountry->currency_id ?? 1;
        } else {
            $currencyId = 1;
        }
        $response['currency_id'] = $currencyId;
        
        if ($client->residency) {
            $residencyCountry = Country::find($client->residency);
            $response['country_code'] = $residencyCountry->iso ?? 'US';
        } else {
            $response['country_code'] = 'US';
        }
        
        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }
    
    public function getProfileById($id)
    {
        $client = Client::findOrFail($id);
        $client->load(['clientSetting', 'businessInfo']);
        
        $response = $client->toArray();
        
        $currencyId = null;
        if ($client->clientSetting && $client->clientSetting->currency_id) {
            $currencyId = $client->clientSetting->currency_id;
        } elseif ($client->residency) {
            $residencyCountry = Country::find($client->residency);
            $currencyId = $residencyCountry->currency_id ?? 1;
        } else {
            $currencyId = 1;
        }
        $response['currency_id'] = $currencyId;
        
        if ($client->residency) {
            $residencyCountry = Country::find($client->residency);
            $response['country_code'] = $residencyCountry->iso ?? 'US';
        } else {
            $response['country_code'] = 'US';
        }
        
        if ($client->businessInfo) {
            $response['register_number'] = $client->businessInfo->reg_number;
        } else {
            $response['register_number'] = null;
        }
        
        return response()->json([
            'success' => true,
            'data' => $response
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

    public function updateProfileById(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'nullable|string|min:2|max:50',
            'last_name' => 'nullable|string|min:2|max:50',
            'job_title' => 'nullable|string|min:2|max:150',
            'mobile_number' => 'nullable|string|max:20',
            'country_code' => 'nullable|exists:countries,id',
            'workplace' => 'nullable|string|max:255',
            'specialty_id' => 'nullable|exists:categories,id',
            'sub_specialty_id' => 'nullable|exists:categories,id',
            'residency' => 'nullable|exists:countries,id',
            'nationality' => 'nullable|exists:countries,id',
            'email' => 'nullable|email|max:255|unique:clients,email,' . $id,
            'register_number' => 'nullable|string|max:100',
            'company_name_en' => 'nullable|string|max:255',
            'company_name_ar' => 'nullable|string|max:255',
            'profile_percentage' => 'nullable|integer|min:0|max:100',
            'currency_id' => 'nullable|exists:currencies,id',
            'language' => 'nullable|string|in:en,ar'
        ]);
        
        $client = Client::findOrFail($id);

        $updateData = array_filter($request->only([
            'first_name', 'last_name', 'job_title', 'mobile_number', 'workplace',
            'specialty_id', 'sub_specialty_id', 'residency', 'nationality', 'email',
            'company_name_en', 'company_name_ar', 'profile_percentage'
        ]));
        
        if ($request->has('mobile_number') && $request->has('country_code')) {
            $country = Country::find($request->country_code);
            if ($country) {
                $updateData['mobile_number'] = $country->phone_prefix . ltrim($request->mobile_number, '+');
            }
        }
        
        try {
            $client->update($updateData);
            
            if ($request->has('register_number')) {
                $businessInfo = ClientBusinessInfo::where('client_id', $client->id)->first();
                if ($businessInfo) {
                    $businessInfo->update(['reg_number' => $request->register_number]);
                } else {
                    ClientBusinessInfo::create([
                        'client_id' => $client->id,
                        'uuid' => $client->uuid,
                        'business_type' => 'subscription',
                        'reg_number' => $request->register_number
                    ]);
                }
            }
            
            if ($request->hasAny(['country_code', 'currency_id', 'language'])) {
                $client->clientSetting()->updateOrCreate(
                    ['client_id' => $client->id],
                    array_filter([
                        'country_id' => $request->country_code,
                        'currency_id' => $request->currency_id,
                        'lang' => $request->language ?? 'en'
                    ])
                );
            }
            
            $client->refresh();
            $client->load('clientSetting', 'businessInfo');
            
            $response = $client->toArray();
            $clientSetting = $client->clientSetting;
            $businessInfo = $client->businessInfo;
            
            if ($clientSetting) {
                $response['country_id'] = $clientSetting->country_id;
                $response['currency_id'] = $clientSetting->currency_id;
                $response['language'] = $clientSetting->lang ?? 'en';
            }
            
            if ($businessInfo) {
                $response['register_number'] = $businessInfo->reg_number;
            }
            
            return response()->json([
                'code' => 200,
                'message' => 'Profile updated successfully',
                'data' => $response
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}