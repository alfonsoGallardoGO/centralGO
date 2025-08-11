<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clase extends Model
{
    use SoftDeletes;
    
    protected $table = 'netsuite_class';

    protected $fillable = [
        'external_id',
        'name',
    ];
}
