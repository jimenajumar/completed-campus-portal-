<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class QuoteController extends Controller
{
    public function index()
    {
        try {
            $quote = Cache::remember(
                'daily_inspiring_quote',
                now()->addHours(12),
                function () {
                    $response = Http::timeout(15)
                        ->get('https://zenquotes.io/api/today');

                    $response->throw();

                    $data = $response->json();

                    if (!isset($data[0]['q'], $data[0]['a'])) {
                        throw new \Exception('Invalid quote response.');
                    }

                    return [
                        'quote' => $data[0]['q'],
                        'author' => $data[0]['a'],
                    ];
                }
            );

            return response()->json([
                'success' => true,
                'data' => $quote,
                'source' => 'ZenQuotes',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to load the inspiring quote.',
            ], 503);
        }
    }
}
