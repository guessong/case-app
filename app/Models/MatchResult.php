<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchResult extends Model
{
    use HasFactory;

    protected $fillable = ['fixture_id', 'home_score', 'away_score', 'is_played'];

    protected $casts = [
        'is_played' => 'boolean',
    ];

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(Fixture::class);
    }
}
