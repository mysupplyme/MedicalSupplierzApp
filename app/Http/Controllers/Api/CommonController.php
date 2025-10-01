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
        $countries = Country::leftJoin('currencies', 'countries.currency_id', '=', 'currencies.id')
            ->select(
                'countries.id',
                'countries.title_en as title',
                'countries.iso',
                'countries.phone_prefix',
                'countries.is_default',
                'currencies.id as currency_id',
                'currencies.is_default as currency_is_default',
                'currencies.rate',
                'currencies.decimal_digits',
                'currencies.code_en as currency_code'
            )
            ->get()
            ->map(function($country) {
                return [
                    'id' => $country->id,
                    'title' => $country->title,
                    'iso' => $country->iso,
                    'phone_prefix' => $country->phone_prefix,
                    'is_default' => $country->is_default,
                    'flag' => 'https://medicalsupplierz.app/assets/flags/' . strtolower($country->iso) . '.png',
                    'currencies' => $country->currency_id ? [[
                        'id' => $country->currency_id,
                        'is_default' => $country->currency_is_default ?? 0,
                        'rate' => $country->rate ?? 1,
                        'decimal_digits' => $country->decimal_digits ?? 2,
                        'code' => $country->currency_code
                    ]] : []
                ];
            });
        
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
    
    public function getCurrencies()
    {
        $currencies = \App\Models\Currency::all(['id', 'title_en as title', 'code_en as code', 'is_default', 'decimal_digits', 'rate']);
        
        return response()->json([
            'code' => 200,
            'message' => 'Success',
            'items' => $currencies
        ]);
    }
}