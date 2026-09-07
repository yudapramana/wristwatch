<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSnapshot;
use Illuminate\Http\JsonResponse;

class GuestController extends Controller
{
    public function getGuestSnapshot(): JsonResponse
    {
        // Ambil snapshot terbaru dengan tipe 'guest'
        $snapshot = EventSnapshot::where('type', 'guest')
            ->whereNotNull('published_at')
            ->latest()
            ->first();

        // Jika snapshot belum ada, buat secara otomatis dari data master event
        if (!$snapshot) {
            // Sesuaikan dengan event_key yang di-insert di database ('MTQXLIPSL')
            $event = Event::where('event_key', 'MTQXLIPSL')->first();

            if (!$event) {
                return response()->json(['message' => 'Data event utama tidak ditemukan.'], 404);
            }

            $snapshot = EventSnapshot::updateOrCreate(
                [
                    'event_id' => $event->id,
                    'type'     => 'guest',
                ],
                [
                    'payload' => [
                        'event_key'        => $event->event_key,
                        'app_name'         => $event->app_name,
                        'event_name'       => $event->event_name,
                        'event_year'       => $event->event_year,
                        'event_location'   => $event->event_location,
                        'event_tagline'    => $event->event_tagline,
                        'start_date'       => $event->start_date,
                        'end_date'         => $event->end_date,
                        'logo_event'       => $event->logo_event,
                        'logo_sponsor_1'   => $event->logo_sponsor_1,
                        'logo_sponsor_2'   => $event->logo_sponsor_2,
                        'logo_sponsor_3'   => $event->logo_sponsor_3,
                    ],
                    'published_at' => now(),
                ]
            );
        }

        // Kembalikan array payload ke frontend Vue
        return response()->json($snapshot->payload);
    }
}