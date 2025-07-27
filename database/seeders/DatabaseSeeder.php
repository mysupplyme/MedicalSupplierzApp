<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@medconf.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Create sample events
        Event::create([
            'title' => 'Advanced Cardiology Workshop',
            'type' => 'workshop',
            'description' => 'Learn the latest techniques in interventional cardiology',
            'date' => '2024-02-15',
            'time' => '09:00',
            'duration' => 480,
            'price' => 299.99,
            'image' => '/images/cardiology-workshop.jpg',
            'speaker' => 'Dr. Sarah Johnson',
            'capacity' => 50,
            'registered' => 23,
            'tags' => ['cardiology', 'workshop', 'advanced'],
            'status' => 'upcoming',
        ]);

        Event::create([
            'title' => 'Neurology Conference 2024',
            'type' => 'conference',
            'description' => 'Annual neurology conference with leading experts',
            'date' => '2024-03-20',
            'time' => '08:00',
            'duration' => 600,
            'price' => 499.99,
            'image' => '/images/neurology-conference.jpg',
            'speaker' => 'Dr. Michael Chen',
            'capacity' => 200,
            'registered' => 156,
            'tags' => ['neurology', 'conference', 'research'],
            'status' => 'upcoming',
        ]);
    }
}