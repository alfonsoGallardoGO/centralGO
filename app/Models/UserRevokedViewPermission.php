<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRevokedViewPermission extends Model
{
    protected $table = 'user_revoked_view_permissions';

    protected $fillable = [
        'user_id',
        'view_name',
        'permission_id',
    ];
}
