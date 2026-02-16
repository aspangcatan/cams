<?php

namespace App\Models\TdhUser;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $connection = 'user';

    protected $table = 'tdh_user.section';

    protected $fillable = [
        'description',
        'head',
        'code',
        'subsection',
    ];
}
