<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prenotazione extends Model
{
    protected $fillable = [
        'campi_id',
        'user_id',
        'data',
        'ora',
        'prezzo',
        'payment_status',
        'payment_intent_id',
    ];

    public function campi()
    {
        return $this->belongsTo(Campi::class);
    }
}
