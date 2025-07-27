<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with(['category', 'specialty', 'subSpecialty']);
        
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->specialty_id) {
            $query->where('specialty_id', $request->specialty_id);
        }
        
        if ($request->sub_specialty_id) {
            $query->where('sub_specialty_id', $request->sub_specialty_id);
        }
        
        if ($request->type) {
            $query->where('type', $request->type);
        }
        
        $events = $query->orderBy('date', 'asc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    public function show(Event $event)
    {
        $event->load(['category', 'specialty', 'subSpecialty']);
        
        return response()->json([
            'success' => true,
            'data' => $event
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:workshop,webinar,conference,exhibition',
            'description' => 'required|string',
            'date' => 'required|date',
            'time' => 'required',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'image' => 'required|string',
            'speaker' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'tags' => 'required|array',
            'category_id' => 'nullable|exists:categories,id',
            'specialty_id' => 'nullable|exists:specialties,id',
            'sub_specialty_id' => 'nullable|exists:sub_specialties,id',
        ]);

        $event = Event::create($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $event
        ], 201);
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'string|max:255',
            'type' => 'in:workshop,webinar,conference,exhibition',
            'description' => 'string',
            'date' => 'date',
            'duration' => 'integer|min:1',
            'price' => 'numeric|min:0',
            'speaker' => 'string|max:255',
            'capacity' => 'integer|min:1',
            'tags' => 'array',
            'status' => 'in:upcoming,ongoing,completed',
            'category_id' => 'nullable|exists:categories,id',
            'specialty_id' => 'nullable|exists:specialties,id',
            'sub_specialty_id' => 'nullable|exists:sub_specialties,id',
        ]);

        $event->update($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $event
        ]);
    }

    public function destroy(Event $event)
    {
        $event->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully'
        ]);
    }

    public function register(Request $request, Event $event)
    {
        if (!$event->canRegister()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot register for this event'
            ], 400);
        }

        $event->increment('registered');
        
        return response()->json([
            'success' => true,
            'message' => 'Successfully registered for event'
        ]);
    }
}