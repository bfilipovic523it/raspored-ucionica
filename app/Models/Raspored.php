<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Raspored extends Model
{
    protected $fillable = [
        'naziv',
        'tip',
        'user_id',
        'fajl_raspored',
        'fajl_dani',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function termini(): HasMany
    {
        return $this->hasMany(Termin::class);
    }
}
