<?php

namespace App\Models\Hris;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $connection = 'user';

    protected $table = 'hris.employees';

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'date_updated';

    protected $fillable = [
        'user_id',
        'birthdate',
        'sex',
        'employee_no',
        'date_hired',
        'employee_type',
    ];
}
