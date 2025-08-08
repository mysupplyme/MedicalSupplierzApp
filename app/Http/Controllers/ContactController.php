<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $data = $request->all();

        try {
            Mail::send('emails.contact-us', ['data' => $data], function ($message) use ($data) {
                $message->to('info@medicalsupplierz.com')
                        ->subject('Contact Form: ' . $data['subject'])
                        ->replyTo($data['email'], $data['name']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Your message has been sent successfully. We will get back to you soon.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message. Please try again.'
            ], 500);
        }
    }
}