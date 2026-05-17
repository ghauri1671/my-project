<?php

namespace App\Jobs;

use App\Mail\NewSignalNotification; // Make sure to import your Mailable
use App\Models\Signal;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail; // Import the Mail facade

class SendNewSignalEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The signal instance.
     *
     * @var \App\Models\Signal
     */
    public $signal;

    /**
     * The subscriber instance.
     *
     * @var \App\Models\Subscriber
     */
    public $subscriber;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\Signal  $signal
     * @param  \App\Models\Subscriber  $subscriber
     * @return void
     */
    public function __construct(Signal $signal, Subscriber $subscriber)
    {
        $this->signal = $signal;
        $this->subscriber = $subscriber;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Mail::to($this->subscriber->email)->send(new NewSignalNotification($this->signal));
    }

    public function failed(\Throwable $exception): void
    {
        // Log the exception details
        \Log::error('SendNewSignalEmail Job Failed', [
            'signal_id' => $this->signal->id ?? 'N/A', // Log signal ID if available
            'subscriber_email' => $this->subscriber->email ?? 'N/A', // Log subscriber email if available
            'exception_message' => $exception->getMessage(),
        ]);

        // You can also send a notification to an administrator here
        // Mail::to('admin@example.com')->send(new JobFailedNotification($this->signal, $this->subscriber, $exception));

        // Or perform any necessary cleanup
    }
}