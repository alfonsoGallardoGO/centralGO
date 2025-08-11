<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    protected $table = 'netsuite_expense_categories';
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'external_id',
        'description',
        'account',
    ];
}
