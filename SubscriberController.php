<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\Signal;
use Illuminate\Http\Request;

class FreeSignalController extends Controller
{
    public function index()
    {
        // Fetch initial signals. Ensure you eager load the 'asset' relationship.
        $signals = Signal::with('asset')->whereIn('group_type',['free','both'])->latest()->paginate(10);

        // Set the subscriber type. This should come from the authenticated user.
        // For this example, we'll hardcode it as 'free'.
        $subscriber_type = "free";

        // Pass the variables to the view.
        return view('frontend.free.index', compact('signals', 'subscriber_type'));
    }

    public function filter(Request $request)
    {
        $query = Signal::query();

        if ($request->has('market_type') && $request->market_type != '') {
            $query->where('market_type', $request->market_type);
        }

        $signals = $query->latest()->paginate(10); // adjust as needed
        $subscriber_type = "free";
        // Return only the table rows
        return response()->json([
            'html' => view('frontend.partials.signal-rows', compact('signals', 'subscriber_type'))->render(),
            'pagination' => $signals->links()->toHtml()
        ]);
    }
}
