<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = json_decode($request->getContent(), true);

        // ==========================================================
        // SCENARIO 1: A NEW USER PAYS (THE FRONT DOOR)
        // ==========================================================
        if ($payload['type'] === 'checkout.session.completed') {
            $session = $payload['data']['object'];
            
            // 🛑 REMINDER: Put your LIVE plink_ ID here!
            $premiumPaymentLinkId = 'plink_1SpTUsEtzDCYetWP2oesj4Ys'; 

            if (isset($session['payment_link']) && $session['payment_link'] === $premiumPaymentLinkId) {
                $email = $session['customer_details']['email'];
                $stripeCustomerId = $session['customer']; // Grab their unique Stripe ID

                $subscriber = Subscriber::where('email', $email)->first();

                if (!$subscriber) {
                    $generatedPassword = Str::random(8);

                    $subscriber = new Subscriber();
                    $subscriber->email = $email;
                    $subscriber->username = $email; 
                    $subscriber->password = Hash::make($generatedPassword);
                    $subscriber->subscriber_type = 'premium';
                    $subscriber->stripe_id = $stripeCustomerId; // Save the ID for later!
                    $subscriber->save();

                    // Queue the Welcome Email
                    if (class_exists(\App\Mail\PremiumWelcomeEmail::class)) {
                        Mail::to($email)->queue(new \App\Mail\PremiumWelcomeEmail($email, $generatedPassword));
                    }
                } else {
                    // If they already exist (e.g. upgrading from free), just update them
                    $subscriber->subscriber_type = 'premium';
                    $subscriber->stripe_id = $stripeCustomerId;
                    $subscriber->save();
                }
            }
        }

        // ==========================================================
        // SCENARIO 2: SUBSCRIPTION CANCELLED OR FAILED (THE BACK DOOR)
        // ==========================================================
        elseif ($payload['type'] === 'customer.subscription.deleted') {
            $subscription = $payload['data']['object'];
            $stripeCustomerId = $subscription['customer'];

            $subscriber = Subscriber::where('stripe_id', $stripeCustomerId)->first();

            if ($subscriber) {
                // Change their status to canceled
                $subscriber->subscriber_type = 'canceled'; // <-- CHANGED THIS
                $subscriber->save();
                
                Log::info('User subscription canceled and locked out: ' . $subscriber->email);
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
}