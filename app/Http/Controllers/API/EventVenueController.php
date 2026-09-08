<?php

namespace App\Http\Controllers\API;

use App\Models\Event;
use App\Models\EventGroup;
use App\Models\EventLocation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EventVenueController extends Controller
{
    public function getVenues(Request $request)
    {
        $eventId = $request->query('event_id', 1);
        $event = Event::findOrFail($eventId);

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

        return response()->json([
            'event'  => $event,
            'venues' => $venues,
        ]);
    }
}