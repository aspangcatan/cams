<?php

namespace App\Models\CentralAccess;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessSystem extends Model
{
    use HasFactory;

    protected $table = 'central_access.systems';

    public const CREATED_AT = 'date_created';

    public const UPDATED_AT = 'date_updated';

    protected $fillable = [
        'system',
        'description',
    ];
}
