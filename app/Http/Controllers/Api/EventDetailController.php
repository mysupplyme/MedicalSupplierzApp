<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventDetailController extends Controller
{
    public function getEventDetails(Request $request, $eventId)
    {
        $event = DB::table('events')
            ->leftJoin('categories as main_cat', 'events.category_id', '=', 'main_cat.id')
            ->leftJoin('categories as sub_cat', 'events.sub_category_id', '=', 'sub_cat.id')
            ->leftJoin('countries', 'events.country_id', '=', 'countries.id')
            ->leftJoin('cities', 'events.city_id', '=', 'cities.id')
            ->select([
                'events.*',
                'main_cat.title_en as category_name',
                'sub_cat.title_en as sub_category_name',
                'countries.title_en as country_name',
                'cities.title_en as city_name'
            ])
            ->where('events.id', $eventId)
            ->first();

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found'
            ], 404);
        }

        // Get event images
        $images = DB::table('event_images')
            ->where('event_id', $eventId)
            ->pluck('image_path')
            ->toArray();

        // Get event speakers
        $speakers = DB::table('event_speakers')
            ->where('event_id', $eventId)
            ->select(['name', 'title', 'bio', 'image'])
            ->get();

        // Get event agenda/schedule
        $agenda = DB::table('event_agenda')
            ->where('event_id', $eventId)
            ->orderBy('start_time')
            ->select(['title', 'description', 'start_time', 'end_time', 'speaker'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $event->id,
                'title' => $event->title_en,
                'description' => $event->description_en,
                'short_description' => $event->short_description_en,
                'category' => $event->category_name,
                'sub_category' => $event->sub_category_name,
                'type' => $event->type, // conference, workshop, expo
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'location' => [
                    'country' => $event->country_name,
                    'city' => $event->city_name,
                    'venue' => $event->venue,
                    'address' => $event->address
                ],
                'pricing' => [
                    'price' => $event->price,
                    'currency' => $event->currency,
                    'early_bird_price' => $event->early_bird_price,
                    'early_bird_deadline' => $event->early_bird_deadline
                ],
                'capacity' => $event->capacity,
                'registered_count' => $event->registered_count ?? 0,
                'status' => $event->status,
                'featured_image' => $event->featured_image,
                'images' => $images,
                'speakers' => $speakers,
                'agenda' => $agenda,
                'website_url' => $event->website_url,
                'contact_email' => $event->contact_email,
                'contact_phone' => $event->contact_phone,
                'tags' => $event->tags ? explode(',', $event->tags) : [],
                'cme_credits' => $event->cme_credits,
                'certificate_provided' => $event->certificate_provided,
                'created_at' => $event->created_at
            ]
        ]);
    }
}