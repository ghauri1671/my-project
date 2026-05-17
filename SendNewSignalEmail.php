<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SignalAutomationController extends Controller
{
    public function handleMT5Signal(Request $request)
    {
        // 1. Verify Authentication
        $secretKey = $request->header('X-EA-KEY');
        if ($secretKey !== env('EA_SECRET_KEY')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // 2. Clean Pair Name (e.g., "EURUSD.m" becomes "EURUSD")
        $rawPair = $request->input('pair');
        $cleanPair = strtoupper(trim(explode('.', $rawPair)[0]));

        // 3. Find Asset using direct DB query
        $asset = DB::table('assets')->where('pair_name', $cleanPair)->first();

        if (!$asset) {
            return response()->json(['error' => 'Asset ' . $cleanPair . ' not found in assets table'], 404);
        }

        // 4. Sync Signal to Database
        try {
            DB::table('signals')->updateOrInsert(
                ['ticket_id' => $request->input('ticket')], // Matches your signals table column
                [
                    'asset_id'   => $asset->id,
                    'type'       => $request->input('type'),
                    'entry'      => $request->input('entry'),
                    'sl'         => $request->input('sl'),
                    'tp'         => $request->input('tp'),
                    'is_active'  => (int)$request->input('is_active'),
                    'result'     => $request->input('result'),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            return response()->json(['success' => true, 'message' => 'Institutional Sync Complete'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server Logic Error: ' . $e->getMessage()], 500);
        }
    }
}