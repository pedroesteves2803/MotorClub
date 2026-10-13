<?php

namespace App\Jobs;

use App\Mail\ShotDeliveryMail;
use App\Models\ShotPaymentEvent;
use App\Models\ShotPurchaseRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendShotDelivery implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $paymentId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $payment = ShotPurchaseRequest::query()->with('buyer', 'grants')->findOrFail($this->paymentId);
        if ($payment->status !== 'approved' || $payment->delivery_sent_at) {
            return;
        }
        Mail::to($payment->buyer)->send(new ShotDeliveryMail($payment));
        $payment->update(['delivery_sent_at' => now()]);
        ShotPaymentEvent::create(['shot_purchase_request_id' => $payment->id, 'event' => 'delivery_email_sent', 'to_status' => 'approved', 'origin' => 'system']);
    }
}
