<?php

namespace App\Models\TdhUser;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPrivilege extends Model
{
    use HasFactory;

    protected $connection = 'user';

    protected $table = 'tdh_user.user_priv';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'syscode',
        'level',
    ];
}
