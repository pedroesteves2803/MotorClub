<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShotPaymentEvent extends Model
{
    protected $guarded = ['id'];

    public function payment()
    {
        return $this->belongsTo(ShotPurchaseRequest::class, 'shot_purchase_request_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
