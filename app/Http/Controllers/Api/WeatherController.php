<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WeatherController extends Controller
{
    public function index()
    {
        try {
            $weather = Cache::remember(
                'aces_tagum_weather',
                now()->addMinutes(10),
                function () {

                    $response = Http::timeout(15)->get(
                        'https://api.open-meteo.com/v1/forecast',
                        [
                            'latitude' => 7.4478,
                            'longitude' => 125.8078,
                            'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,is_day,precipitation,weather_code,wind_speed_10m',
                            'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,sunrise,sunset,precipitation_probability_max',
                            'forecast_days' => 3,
                            'timezone' => 'Asia/Manila',
                        ]
                    );

                    if ($response->failed()) {
                        throw new \Exception('Weather provider request failed.');
                    }

                    return $response->json();
                }
            );

            return response()->json([
                'success' => true,
                'location' => 'Tagum City, Philippines',
                'data' => $weather,
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve weather information.',
            ], 503);
        }
    }
}
