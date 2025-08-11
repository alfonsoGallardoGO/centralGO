<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'netsuite_accounting_accounts';

    protected $fillable = [
        'external_id',
        'account_number',
        'name',
        'type',
        'description',
        'currency',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

}
