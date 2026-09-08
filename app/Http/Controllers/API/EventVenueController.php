<?php

namespace App\Http\Controllers\API;

use App\Models\Event;
use App\Models\EventGroup;
use App\Models\EventSnapshot;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EventVenueController extends Controller
{
    public function getVenues(Request $request)
    {
        $eventId = $request->query('event_id', 1);

        // Ambil snapshot terbaru dengan tipe 'venue' untuk event terkait
        $snapshot = EventSnapshot::where('event_id', $eventId)
            ->where('type', 'venue')
            ->whereNotNull('published_at')
            ->latest()
            ->first();

        // Jika snapshot belum ada, buat secara otomatis dari data master event dan venue
        if (!$snapshot) {
            $event = Event::find($eventId);

            if (!$event) {
                return response()->json(['message' => 'Data event tidak ditemukan.'], 404);
            }

            $rawGroups = EventGroup::query()
                ->where('event_id', $event->id)
                ->where('status', 'active')
                ->whereNotNull('event_judge_panel_id')
                ->with(['judgePanel.location'])
                ->orderBy('order_number')
                ->get();

            $venues = $rawGroups
                ->groupBy(function ($group) {
                    return optional(optional($group->judgePanel)->location)->id ?? 0;
                })
                ->sortKeys()
                ->map(function ($items, $locationId) {
                    $panel    = $items->first()->judgePanel;
                    $location = $panel?->location;

                    $mapsUrl = null;
                    if ($location && $location->latitude && $location->longitude) {
                        $mapsUrl = "https://www.google.com/maps?q={$location->latitude},{$location->longitude}";
                    }

                    return [
                        'location_id'  => $locationId,
                        'tempat_lomba' => $location->name ?? 'Lokasi Belum Ditentukan',
                        'address'      => $location->address ?? null,
                        'photo_url'    => $location->photo_url ?? null,
                        'maps_url'     => $mapsUrl,
                        'qr_code_url'  => $mapsUrl
                            ? 'data:image/svg+xml;base64,' . base64_encode(
                                QrCode::format('svg')->margin(1)->size(220)->generate($mapsUrl)
                            )
                            : null,
                        'cabang' => $items->map(function ($group) {
                            $panel = $group->judgePanel;
                            return [
                                'bidang_lomba' => $group->full_name,
                                'majelis'      => $panel->roman_name ?? $panel->name ?? null,
                            ];
                        })->values(),
                    ];
                })
                ->values();

            // Susun payload yang akan disimpan
            $payload = [
                'event'  => $event,
                'venues' => $venues,
            ];

            // Simpan ke EventSnapshot
            $snapshot = EventSnapshot::updateOrCreate(
                [
                    'event_id' => $event->id,
                    'type'     => 'venue',
                ],
                [
                    'payload'      => $payload,
                    'published_at' => now(),
                ]
            );
        }

        // Kembalikan array payload ke frontend Vue
        return response()->json($snapshot->payload);
    }
}