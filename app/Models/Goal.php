<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'name', 'description', 'target_amount', 'current_amount', 'target_date', 'priority'])]
#[Hidden(['user_id'])]
class Goal extends Model
{
    /** @use HasFactory<GoalFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'target_amount',
        'current_amount',
        'target_date',
        'priority',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'target_date' => 'date',
    ];

    /**
     * The user that owns the goal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate the percentage completion of the goal.
     */
    public function percentage(): float
    {
        if ($this->target_amount == 0) {
            return 0.0;
        }

        return round(($this->current_amount / $this->target_amount) * 100, 1);
    }

    /**
     * Check if the goal is completed.
     */
    public function isCompleted(): bool
    {
        return $this->current_amount >= $this->target_amount;
    }
}
