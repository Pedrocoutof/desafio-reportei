<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommitView extends Model
{
    use HasFactory;
    protected $table = "commits_view";

    protected $primaryKey = null;

    public $timestamps = false;

    protected $fillable = [
        'number_commits',
        'created_at_date',
        'repository_id',
        'author_name'
    ];

}
