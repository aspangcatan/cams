<?php

namespace App\Models\TdhUser;

use App\Models\Hris\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserAccount extends Model
{
    use HasFactory;

    protected $connection = 'user';

    protected $table = 'tdh_user.users';

    protected $fillable = [
        'fname',
        'mname',
        'lname',
        'suffix',
        'username',
        'password',
        'designation',
        'division',
        'section',
        'status',
        'is_deployed',
    ];

    protected $hidden = [
        'password',
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id', 'id');
    }

    public function setPasswordAttribute($value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $passwordInfo = password_get_info((string) $value);
        $this->attributes['password'] = $passwordInfo['algo'] ? (string) $value : Hash::make((string) $value);
    }
}
