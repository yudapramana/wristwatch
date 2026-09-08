<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventBranch;
use App\Models\EventGroup;
use App\Models\EventCategory;
use App\Models\EventSnapshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HierarkiMtqController extends Controller
{
    /**
     * GET /api/hierarki-mtq
     * Menampilkan hierarki Cabang -> Golongan -> Kategori dalam format JSON
     * untuk dikonsumsi oleh competition-groups.vue
     */
    public function index(Request $request): JsonResponse
    {
        $eventId = $request->query('event_id', 1);

        // Ambil snapshot terbaru dengan tipe 'hierarki' untuk event terkait
        $snapshot = EventSnapshot::where('event_id', $eventId)
            ->where('type', 'hierarki')
            ->whereNotNull('published_at')
            ->latest()
            ->first();

        // Jika snapshot belum ada, buat secara otomatis dari data master
        if (!$snapshot) {
            $event = Event::find($eventId);

            if (!$event) {
                return response()->json(['message' => 'Data event tidak ditemukan.'], 404);
            }

            // 1. Ambil semua data Branch, Group, Category (dengan relasi master data)
            $eventBranches = EventBranch::with('branch', 'event')
                ->where('event_id', $event->id)
                ->get();

            $eventGroups = EventGroup::with('group')
                ->where('event_id', $event->id)
                ->where('status', 'active')
                ->get();

            $eventCategories = EventCategory::with('category')
                ->where('event_id', $event->id)
                ->get();

            // 2. Kelompokkan Group berdasarkan event_id & branch_id
            $groupedGroups = $eventGroups->groupBy(function ($item) {
                return $item->event_id . '-' . $item->branch_id;
            });

            // 3. Kelompokkan Category berdasarkan event_id, branch_id, & group_id
            $groupedCategories = $eventCategories->groupBy(function ($item) {
                return $item->event_id . '-' . $item->branch_id . '-' . $item->group_id;
            });

            // 4. Susun struktur JSON hierarkis
            $branches = $eventBranches->map(function ($branch) use ($groupedGroups, $groupedCategories) {
                $branchName = $branch->full_name ?? ($branch->branch->name ?? $branch->branch_name ?? '-');

                $groupKey = $branch->event_id . '-' . $branch->branch_id;
                $groups = $groupedGroups->get($groupKey, collect());

                $groupsData = $groups->map(function ($group) use ($groupedCategories) {
                    $groupName = $group->full_name ?? ($group->group->name ?? $group->group_name ?? '-');

                    $categoryKey = $group->event_id . '-' . $group->branch_id . '-' . $group->group_id;
                    $categories = $groupedCategories->get($categoryKey, collect());

                    $categoriesData = $categories->map(function ($category) {
                        $categoryName = $category->full_name ?? ($category->category->name ?? $category->category_name ?? '-');

                        return [
                            'category_name' => $categoryName,
                        ];
                    })->values();

                    return [
                        'group_name' => $groupName,
                        'categories' => $categoriesData,
                    ];
                })->values();

                return [
                    'branch_name' => $branchName,
                    'groups' => $groupsData,
                ];
            })->values();

            // 5. Susun payload yang akan disimpan
            $payload = [
                'event' => [
                    'event_name' => $event->event_name ?? $event->name ?? null,
                    'event_year' => $event->event_year ?? $event->year ?? null,
                ],
                'branches' => $branches,
            ];

            // 6. Simpan ke EventSnapshot
            $snapshot = EventSnapshot::updateOrCreate(
                [
                    'event_id' => $event->id,
                    'type'     => 'hierarki',
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