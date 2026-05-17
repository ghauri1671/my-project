<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('frontend.login.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = Subscriber::where('email', $credentials['email'])->first();

        // 1. Check if user exists and password is correct
        if ($user && ($user->password === $credentials['password'] || Hash::check($credentials['password'], $user->password))) {
            
            // 🛑 THE NEW VIP CHECK: Ensure they are actually Premium!
            if ($user->subscriber_type !== 'premium') {
                return back()->withErrors(['email' => 'Your Premium Subscription has expired or was canceled. Please renew to access the dashboard.']);
            }

            // Login successful!
            session(['subscriber_user_id' => $user->id]);
            session(['subscriber_user_name' => $user->username]);
            return redirect()->route('frontend.signal.index');
        }

        // Login failed (Wrong email or password)
        return back()->withErrors(['email' => 'Invalid credentials']);
    }
}