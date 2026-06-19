<?php

namespace App\Http\Controllers\Api;

use App\Contracts\SpotServiceInterface;
use App\DTOs\SpotStatus;
use App\Http\Controllers\Controller;
use App\Models\SpotBatch;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class SpotController extends Controller
{
    public function __construct(
        private readonly SpotServiceInterface $spotService,
    ) {}

    public function remaining(): JsonResponse
    {
        $total = (int) SpotBatch::sum('total_spots');
        $remaining = $this->spotService->remaining();

        $day = config('spots.replenish_day', 'monday');
        $time = config('spots.replenish_time', '08:00');

        $status = new SpotStatus(
            remaining: $remaining,
            total: $total,
            nextReplenish: $this->calculateNextReplenish($day, $time),
        );

        return response()->json([
            'remaining' => $status->remaining,
            'total' => $status->total,
            'next_replenish' => $status->nextReplenish,
        ]);
    }

    private function calculateNextReplenish(string $day, string $time): string
    {
        $dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $dayIndex = array_search(strtolower($day), $dayNames, true);
        $dayIndex = $dayIndex !== false ? $dayIndex : Carbon::MONDAY;

        $now = Carbon::now();
        $todayReplenish = $now->copy()->startOfDay()->setTimeFromTimeString($time);

        if ($now->dayOfWeek === $dayIndex && $now->lessThan($todayReplenish)) {
            return $todayReplenish->toIso8601String();
        }

        return $now->copy()->next($dayIndex)->setTimeFromTimeString($time)->toIso8601String();
    }
}
