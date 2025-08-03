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
        ]);

        $client = Client::where('email', $request->email)
                       ->where('type', 'buyer')
                       ->first();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $client,
                'token' => 'simple-token-' . $client->id
            ]
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:clients',
            'mobile_number' => 'required|string|max:20',
            'company_name_en' => 'nullable|string|max:150',
            'workplace' => 'nullable|string',
            'specialty_id' => 'required|exists:categories,id',
            'sub_specialty_id' => 'nullable|exists:categories,id',
            'nationality' => 'nullable|exists:countries,id',
            'residency' => 'nullable|exists:countries,id',
        ]);

        $nameParts = explode(' ', $request->name, 2);
        $firstName = $nameParts[0];
        $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
        
        $client = Client::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'type' => 'buyer',
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'company_name_en' => $request->company_name_en,
            'workplace' => $request->workplace,
            'specialty_id' => $request->specialty_id,
            'sub_specialty_id' => $request->sub_specialty_id,
            'nationality' => $request->nationality,
            'residency' => $request->residency,
            'buyer_type' => 'doctor',
            'is_buyer' => 1,
            'status' => 1,
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

        $client = Client::where('email', $request->email)
                       ->where('type', 'buyer')
                       ->first();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Email not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reset instructions sent to your email'
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
            'specialty_id' => 'exists:categories,id',
            'sub_specialty_id' => 'nullable|exists:categories,id',
            'residency' => 'exists:countries,id',
            'nationality' => 'exists:countries,id',
        ]);

        $client = $request->get('auth_user');
        $client->update($request->only([
            'mobile_number', 'specialty_id', 'sub_specialty_id', 
            'residency', 'nationality'
        ]));

        return response()->json([
            'success' => true,
            'data' => $client
        ]);
    }

    public function changePassword(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Password change not supported for this user type'
        ]);
    }
}