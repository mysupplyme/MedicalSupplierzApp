<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:clients',
            'phone' => 'required|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'specialty_id' => 'required|exists:categories,id',
            'sub_specialty_id' => 'nullable|exists:categories,id',
        ]);

        $nameParts = explode(' ', $request->name, 2);
        $firstName = $nameParts[0];
        $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
        
        $client = Client::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $request->email,
            'phone' => $request->phone,
            'company' => $request->company,
            'address' => $request->address,
            'specialty_id' => $request->specialty_id,
            'sub_specialty_id' => $request->sub_specialty_id,
            'buyer_type' => 'doctor',
        ]);

        return response()->json([
            'success' => true,
            'data' => $client,
            'message' => 'Doctor registered successfully'
        ], 201);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return response()->json([
            'success' => $status === Password::RESET_LINK_SENT,
            'message' => $status === Password::RESET_LINK_SENT ? 'Reset link sent' : 'Unable to send reset link'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function getProfile(Request $request)
    {
        $user = $request->user()->load(['specialty', 'subSpecialty', 'residencyCountry', 'nationalityCountry']);
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'string|max:255',
            'phone' => 'string|max:20',
            'specialty_id' => 'exists:specialties,id',
            'sub_specialty_id' => 'nullable|exists:sub_specialties,id',
            'residency_country_id' => 'exists:countries,id',
            'nationality_country_id' => 'exists:countries,id',
        ]);

        $user = $request->user();
        $user->update($request->only([
            'name', 'phone', 'specialty_id', 'sub_specialty_id', 
            'residency_country_id', 'nationality_country_id'
        ]));

        return response()->json([
            'success' => true,
            'data' => $user->load(['specialty', 'subSpecialty', 'residencyCountry', 'nationalityCountry'])
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }
}