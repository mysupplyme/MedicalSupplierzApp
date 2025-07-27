<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use App\Models\SubSpecialty;
use App\Models\Country;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function getSpecialties()
    {
        $specialties = Specialty::where('is_active', true)->get(['id', 'name']);
        
        return response()->json([
            'success' => true,
            'data' => $specialties
        ]);
    }

    public function getSubSpecialties(Request $request)
    {
        $specialtyId = $request->specialty_id;
        
        $subSpecialties = SubSpecialty::where('specialty_id', $specialtyId)
            ->where('is_active', true)
            ->get(['id', 'name']);
        
        return response()->json([
            'success' => true,
            'data' => $subSpecialties
        ]);
    }

    public function getResidencies()
    {
        $countries = Country::all(['id', 'title_en as name', 'iso as code']);
        
        return response()->json([
            'success' => true,
            'data' => $countries
        ]);
    }

    public function getNationalities()
    {
        $countries = Country::all(['id', 'title_en as name', 'iso as code']);
        
        return response()->json([
            'success' => true,
            'data' => $countries
        ]);
    }

    public function getCountryCodes()
    {
        $countries = Country::all(['id', 'title_en as name', 'iso as code', 'phone_prefix as phone_code']);
        
        return response()->json([
            'success' => true,
            'data' => $countries
        ]);
    }
}