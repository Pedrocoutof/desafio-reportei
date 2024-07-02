<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commit extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'sha',
        'repository_id',
        'author_name',
        'created_at'
    ];

    public function repository() :BelongsTo {
        return $this->belongsTo(Repository::class);
    }

}
