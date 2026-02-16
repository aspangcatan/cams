<?php

namespace App\Models\TdhUser;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    protected $connection = 'user';

    protected $table = 'tdh_user.division';

    protected $fillable = [
        'description',
        'head',
        'code',
    ];
}
