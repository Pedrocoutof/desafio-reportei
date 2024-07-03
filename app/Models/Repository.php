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

    /**
     * @param $userId
     * @param $repository
     * @param $relationships
     * @return \Illuminate\Database\Eloquent\Builder|Model
     */
    static function getCommitsGrouped($userId, $repository)
    {
        $repository = Repository::getRepository($userId, $repository);

        if ($repository) {
            $commits = DB::select('
            SELECT COUNT(*) AS number_commits, DATE(created_at) AS created_at_date , author_name
            FROM commits
            WHERE repository_id = ?
            GROUP BY DATE(created_at), author_name
            ORDER BY created_at_date
        ', [$repository->id]);

            $repository->setAttribute('commits', $commits);
        }

        return $repository ?? null;
    }

}
