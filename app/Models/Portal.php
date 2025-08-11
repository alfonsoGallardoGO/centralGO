<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Portal extends Model
{
    use SoftDeletes;

    protected $table = 'portals';

    protected $fillable = [
        'name',
        'url',
        'description',
        'status',
    ];
}
