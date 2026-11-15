<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        $year = $validated['year'] ?? now()->year;

        try {
            $holidays = Cache::remember(
                "philippine_holidays_{$year}",
                now()->addHours(12),
                function () use ($year) {
                    $response = Http::timeout(15)->get(
                        "https://date.nager.at/api/v3/PublicHolidays/{$year}/PH"
                    );

                    $response->throw();

                    return $response->json();
                }
            );

            return response()->json([
                'success' => true,
                'year' => $year,
                'data' => $holidays,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve Philippine holidays. Please try again later.',
            ], 503);
        }
    }
}
