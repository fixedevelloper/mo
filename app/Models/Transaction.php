<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    /**
     * Champs remplissables en masse
     */
    protected $fillable = [
        'phone',
        'operator',
        'amount',
        'type',
        'sms_status',
        'status',
        'device_id',
        'raw_sms',
        'operator_code',
        'processed_at',
    ];

    /**
     * Casts pour les types
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    /**
     * Vérifie si la transaction est en attente
     */
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    /**
     * Vérifie si la transaction est en cours de traitement
     */
    public function isProcessing(): bool
    {
        return $this->status === 'PROCESSING';
    }

    /**
     * Vérifie si la transaction a réussi
     */
    public function isSuccess(): bool
    {
        return $this->status === 'SUCCESS';
    }

    /**
     * Vérifie si la transaction a échoué
     */
    public function isFailed(): bool
    {
        return $this->status === 'FAILED';
    }

    /**
     * Relation avec le device utilisé (optionnel)
     */
    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id', 'id');
    }

    /**
     * Retourne le code USSD généré pour debug
     */
    public function getUssdCode(): ?string
    {
        return $this->operator_code;
    }
}
