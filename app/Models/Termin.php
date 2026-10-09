<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Termin extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ucionica_id',
        'user_id',
        'created_by',
        'updated_by',
        'naziv',
        'opis',
        'datum',
        'vreme_pocetka',
        'vreme_kraja',
        'bulk_uid',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'ucionica_id' => 'integer',
            'user_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'datum' => 'date',
        ];
    }

    public function ucionica(): BelongsTo
    {
        return $this->belongsTo(Ucionica::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
