<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Signal;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $activeSubscriberCount = Subscriber::where('expire_date', '>', Carbon::now())->count();
        $totalSubscriberCount = Subscriber::count();
        $inActiveSubscriberCount = Subscriber::where('expire_date', '<=', Carbon::now())->count();
        $totalSignalCount = Signal::count();
        $totalAssetCount = Asset::count();



        return view('pages.dashboard', [
            'activeSubscriberCount' => $activeSubscriberCount,
            'totalSubscriberCount' => $totalSubscriberCount,
            'inActiveSubscriberCount' => $inActiveSubscriberCount,
            'totalSignalCount' => $totalSignalCount,
            'totalAssetCount' => $totalAssetCount,
        ]);
    }

}
