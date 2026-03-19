<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $table = 'devices';

    protected $fillable = [
        'name',
        'phone_number',
        'operator',
        'status',
    ];

    /**
     * Relation avec les transactions exécutées sur ce device
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'device_id', 'id');
    }

    /**
     * Vérifie si le device est actif
     */
    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }
}
