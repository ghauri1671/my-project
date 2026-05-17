<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TradingJournalController extends Controller
{
    public function index()
    {
        return view('trading-journal.index');
    }
}