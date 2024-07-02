<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Repository extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'name',
        'owner_id',
        'last_synced'
    ];

    function owner() : BelongsTo {
        return $this->belongsTo(User::class, 'owner', 'id');
    }

    function commits() : HasMany {
        return $this->HasMany(Commit::class);
    }

    static function getRepository($userId, $repository, $relationships = []): \Illuminate\Database\Eloquent\Builder|Model|null
    {
        $relationships[] = 'owner';
        $repository = Repository::with($relationships)
            ->where('owner', '=', $userId)
            ->where('name', '=', $repository)
            ->first();

        return $repository ?? null;
    }
}
