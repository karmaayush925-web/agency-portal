<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'manager_id',
        'title',
        'description',
        'hourly_rate',
        'status',
        'deadline',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'deadline' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}