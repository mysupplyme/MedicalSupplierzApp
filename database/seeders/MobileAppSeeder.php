<?php

namespace Database\Seeders;

use App\Models\Specialty;
use App\Models\SubSpecialty;
use App\Models\Country;
use App\Models\Category;
use Illuminate\Database\Seeder;

class MobileAppSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Specialties
        $specialties = [
            'Cardiology', 'Neurology', 'Orthopedics', 'Pediatrics', 'Dermatology',
            'Psychiatry', 'Radiology', 'Anesthesiology', 'Emergency Medicine'
        ];

        foreach ($specialties as $specialty) {
            Specialty::create(['name' => $specialty]);
        }

        // Seed Sub-specialties
        $subSpecialties = [
            'Cardiology' => ['Interventional Cardiology', 'Electrophysiology', 'Heart Failure'],
            'Neurology' => ['Stroke', 'Epilepsy', 'Movement Disorders'],
            'Orthopedics' => ['Sports Medicine', 'Spine Surgery', 'Joint Replacement']
        ];

        foreach ($subSpecialties as $specialtyName => $subs) {
            $specialty = Specialty::where('name', $specialtyName)->first();
            foreach ($subs as $sub) {
                SubSpecialty::create([
                    'specialty_id' => $specialty->id,
                    'name' => $sub
                ]);
            }
        }

        // Seed Countries
        $countries = [
            ['name' => 'United States', 'code' => 'US', 'phone_code' => '+1'],
            ['name' => 'United Kingdom', 'code' => 'GB', 'phone_code' => '+44'],
            ['name' => 'Canada', 'code' => 'CA', 'phone_code' => '+1'],
            ['name' => 'Australia', 'code' => 'AU', 'phone_code' => '+61'],
            ['name' => 'Germany', 'code' => 'DE', 'phone_code' => '+49'],
            ['name' => 'France', 'code' => 'FR', 'phone_code' => '+33'],
            ['name' => 'India', 'code' => 'IN', 'phone_code' => '+91'],
            ['name' => 'Japan', 'code' => 'JP', 'phone_code' => '+81'],
            ['name' => 'China', 'code' => 'CN', 'phone_code' => '+86'],
            ['name' => 'Brazil', 'code' => 'BR', 'phone_code' => '+55'],
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }

        // Seed Categories
        $categories = [
            ['name' => 'Medical Conference', 'slug' => 'medical-conference', 'description' => 'General medical conferences'],
            ['name' => 'Surgical Workshop', 'slug' => 'surgical-workshop', 'description' => 'Hands-on surgical training'],
            ['name' => 'Research Symposium', 'slug' => 'research-symposium', 'description' => 'Latest medical research'],
            ['name' => 'Clinical Training', 'slug' => 'clinical-training', 'description' => 'Clinical skills development'],
            ['name' => 'Technology in Medicine', 'slug' => 'technology-medicine', 'description' => 'Medical technology advances'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}