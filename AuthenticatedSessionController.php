<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Mail\SubscriberCredntialMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class SubscriberController extends Controller
{
    public function index()
    {
        return view('pages.subscribers.index');
    }

    public function store(Request $request)
    {
        // Validate like Breeze, but keep your username field
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required'],
        ]);


        // Create user as a subscriber
        $user = User::create([
            'name' => $validated['username'],
            'email' => $validated['email'],
            'raw' => $validated['password'],
            'password' => Hash::make($validated['password']),
            'role' => 'subscriber',
        ]);


        // Send credential email (raw password) – be cautious in production
        try {
            Mail::to($user->email)->send(new SubscriberCredntialMail($user, $validated['password']));
        } catch (\Throwable $e) {
            report($e); // don't block signup if mail fails
        }

        // Auto-login and go to subscriber area
        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Subscriber created successfully',
            'data' => $user
        ]);
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $Subscribers = User::orderBy('id', 'desc')->get();

            return DataTables::of($Subscribers)
                ->addIndexColumn()
                ->addColumn('serial_number', function ($Subscriber) {
                    return $Subscriber->id;
                })
                ->addColumn('username', function ($Subscriber) {
                    return $Subscriber->name;
                })
                ->addColumn('email', function ($Subscriber) {
                    return $Subscriber->email;

                    $badgeClass = $Subscriber->Subscriber_type === 'Buy/Long' ? 'bg-success' : 'bg-danger';
                    return '<span class="badge ' . $badgeClass . '">' . $Subscriber->Subscriber_type . '</span>';
                })
                ->addColumn('password', function ($Subscriber) {
                    return $Subscriber->raw ?? "";

                    return number_format($Subscriber->entry_price, 5);
                })
                ->addColumn('role', function ($Subscriber) {
                    return $Subscriber->role ?? "";

                    return number_format($Subscriber->entry_price, 5);
                })
                ->addColumn('created_at', function ($Subscriber) {
                    return $Subscriber->created_at
                        ? $Subscriber->created_at->format('Y-m-d H:i:s')
                        : 'N/A';
                    return number_format($Subscriber->take_profit, 5);
                })


                ->addColumn('action', function ($Subscriber) {
                    $editBtn = '<button class="btn btn-sm btn-primary edit-Subscriber" data-id="' . $Subscriber->id . '" data-bs-toggle="modal" data-bs-target="#SubscriberModal">Edit</button>';
                    $deleteBtn = '<button class="btn btn-sm btn-danger delete-Subscriber" data-id="' . $Subscriber->id . '">Delete</button>';
                    $statusBtn = $Subscriber->is_open
                        ? '<button class="btn btn-sm btn-secondary toggle-status" data-id="' . $Subscriber->id . '">Close</button>'
                        : '<button class="btn btn-sm btn-success toggle-status" data-id="' . $Subscriber->id . '">Reopen</button>';
                    // return $editBtn . ' ' . $deleteBtn . ' ' . $statusBtn;
                    return $editBtn . ' ' . $deleteBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('pages.Subscribers.index'); // Make sure this matches your view path
    }


    public function edit($id)
    {
        $subscriber = Subscriber::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $subscriber
        ]);
    }

    public function update(Request $request, Subscriber $subscriber)
    {

        // dd($request->all());

        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required',
            'password' => 'required',

        ]);

        // dd($validated);

        try {
            $subscriber = $subscriber->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Subscriber updated successfully',
                'data' => $subscriber
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating subscriber: ' . $e->getMessage()
            ], 500);
        }
    }
}
