<?php

namespace App\Services;

use App\Jobs\SendShotDelivery;
use App\Models\ShotDownloadGrant;
use App\Models\ShotPaymentEvent;
use App\Models\ShotPurchaseRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShotPaymentManager
{
    public function approve(ShotPurchaseRequest $payment, User $admin, ?string $reason = null): void
    {
        $this->change($payment, $admin, 'approved', $reason);
    }

    public function reject(ShotPurchaseRequest $payment, User $admin, string $reason): void
    {
        $this->change($payment, $admin, 'rejected', $reason);
    }

    public function refund(ShotPurchaseRequest $payment, User $admin, string $reason): void
    {
        $this->change($payment, $admin, 'refunded', $reason);
    }

    private function change(ShotPurchaseRequest $payment, User $admin, string $next, ?string $reason): void
    {
        if (! $admin->isSuperAdmin()) {
            throw ValidationException::withMessages(['payment' => 'Apenas Super Admin pode decidir pagamentos.']);
        }
        DB::transaction(function () use ($payment, $admin, $next, $reason) {
            $payment = ShotPurchaseRequest::query()->lockForUpdate()->findOrFail($payment->id);
            $allowed = $next === 'refunded' ? $payment->status === 'approved' && $payment->decided_at !== null : $payment->decided_at === null && in_array($payment->status, ['pending', 'approved'], true);
            if (! $allowed) {
                throw ValidationException::withMessages(['payment' => 'Transição de pagamento inválida.']);
            }
            if (in_array($next, ['rejected', 'refunded'], true) && blank($reason)) {
                throw ValidationException::withMessages(['reason' => 'Informe a justificativa.']);
            }
            $previous = $payment->status;
            $payment->update(['status' => $next, 'decided_by' => $admin->id, 'decided_at' => now(), 'decision_reason' => $reason, 'authorized_at' => $next === 'approved' ? now() : $payment->authorized_at, 'refunded_at' => $next === 'refunded' ? now() : null]);
            ShotPaymentEvent::create(['shot_purchase_request_id' => $payment->id, 'event' => "payment_{$next}", 'from_status' => $previous, 'to_status' => $next, 'admin_id' => $admin->id, 'reason' => $reason, 'origin' => 'admin']);
            if ($next === 'approved') {
                ShotDownloadGrant::firstOrCreate(['shot_purchase_request_id' => $payment->id, 'shot_photo_id' => $payment->shot_photo_id], ['granted_at' => now()]);
                ShotPaymentEvent::create(['shot_purchase_request_id' => $payment->id, 'event' => 'photos_released', 'to_status' => $next, 'admin_id' => $admin->id, 'origin' => 'system']);
                SendShotDelivery::dispatch($payment->id)->afterCommit();
            }
            if ($next === 'refunded') {
                $payment->grants()->whereNull('revoked_at')->update(['revoked_at' => now()]);
                ShotPaymentEvent::create(['shot_purchase_request_id' => $payment->id, 'event' => 'access_revoked', 'from_status' => 'approved', 'to_status' => 'refunded', 'admin_id' => $admin->id, 'reason' => $reason, 'origin' => 'system']);
            }
        });
    }
}
