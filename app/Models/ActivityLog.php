<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'ip_address',
    ];

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an enterprise audit log entry.
     */
    public static function record(string $action, Model $subject, string $description): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => class_basename($subject),
            'subject_id' => $subject->getKey(),
            'description' => $description,
            'ip_address' => request()?->ip(),
        ]);
    }
}