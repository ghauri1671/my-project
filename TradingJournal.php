<?php

namespace App\Jobs;

use App\Mail\SignalUpdateNotification; // Import the new Mailable
use App\Models\Signal;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log; // Import the Log facade for failed method

class SendSignalUpdateEmail implements ShouldQueue
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
        Mail::to($this->subscriber->email)->send(new SignalUpdateNotification($this->signal));
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendSignalUpdateEmail Job Failed', [
            'signal_id' => $this->signal->id ?? 'N/A',
            'subscriber_email' => $this->subscriber->email ?? 'N/A',
            'exception_message' => $exception->getMessage(),
            'exception_trace' => $exception->getTraceAsString(),
        ]);
    }
}