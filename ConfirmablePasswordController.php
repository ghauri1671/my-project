<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB; 
use Session;// <-- add this

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        \Log::info("Login attempt", ['email' => $request->email]);
        $request->authenticate();

        $request->session()->regenerate();

        Auth::logoutOtherDevices($request->input('password'));
        if (config('session.driver') === 'database') {
            DB::table('sessions')
                ->where('user_id', $request->user()->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }



        // 3) Role-based redirect
        $user = $request->user();
        \Log::info("Authenticated user", ['id' => $user->id, 'role' => $user->role]);

        if ($user->role === 'admin') {
            return redirect()->intended(route('dashboard'));
        }

        if ($user->role === 'subscriber') {
            // Your custom session data for subscribers
            session([
                'subscriber_user_id' => $user->id,
                'subscriber_user_name' => $user->name,
            ]);

            \Log::info(Session::all());





            return redirect()->intended(route('frontend.signal.index'));
        }


        // Unknown role — log out and show error
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();



        return back()->withErrors([
            'email' => 'Your account role is not allowed to sign in.',
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
