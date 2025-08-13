<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vista extends Model
{
    protected $table = 'system_views';

    protected $fillable = [
        'name',
        'modulo',
        'created_at',
        'updated_at',
    ];
}
