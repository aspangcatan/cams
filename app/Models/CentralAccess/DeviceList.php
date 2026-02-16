<?php

namespace App\Models\CentralAccess;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceList extends Model
{
    use HasFactory;

    protected $table = 'central_access.device_lists';

    protected $fillable = [
        'userid',
        'android_id',
    ];
}
