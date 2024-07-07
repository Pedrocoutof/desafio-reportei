<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Repository extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'name',
        'owner_id',
        'last_synced'
    ];

    /**
     * @return BelongsTo
     */
    function owner() : BelongsTo {
        return $this->belongsTo(User::class, 'owner', 'id');
    }

    /**
     * @return HasMany
     */
    function commits() : HasMany {
        return $this->HasMany(Commit::class);
    }

}
