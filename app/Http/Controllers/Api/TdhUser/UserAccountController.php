<?php

namespace App\Http\Controllers\Api\TdhUser;

use App\Http\Controllers\Api\CrudController;
use App\Models\Hris\Employee;
use App\Models\TdhUser\UserAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UserAccountController extends CrudController
{
    protected string $modelClass = UserAccount::class;

    protected array $storeRules = [
        'fname' => 'required|string|max:255',
        'mname' => 'nullable|string|max:255',
        'lname' => 'required|string|max:255',
        'suffix' => 'nullable|string|max:50',
        'username' => 'required|string|max:255',
        'password' => 'required|string|min:4|max:255',
        'designation' => 'nullable|integer',
        'division' => 'nullable|integer',
        'section' => 'nullable|integer',
        'is_deployed' => 'nullable|integer|in:1,2',
        'birthdate' => 'nullable|date',
        'sex' => 'nullable|in:MALE,FEMALE',
        'employee_no' => 'nullable|string|max:50',
        'date_hired' => 'nullable|date',
        'employee_type' => 'nullable|in:Job Order,Permanent,Resigned,Retired,Temporary,EOT,COS',
    ];

    public function index(Request $request): JsonResponse
    {
        $query = UserAccount::query()
            ->leftJoin('hris.employees as employee', 'employee.user_id', '=', 'tdh_user.users.id')
            ->select([
                'tdh_user.users.*',
                'employee.birthdate',
                'employee.sex',
                'employee.employee_no',
                'employee.date_hired',
                'employee.employee_type',
            ])
            ->orderByDesc('tdh_user.users.id');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($builder) use ($like) {
                $builder->where('fname', 'like', $like)
                    ->orWhere('mname', 'like', $like)
                    ->orWhere('lname', 'like', $like)
                    ->orWhereRaw("CONCAT_WS(' ', fname, mname, lname, suffix) LIKE ?", [$like])
                    ->orWhere('employee.employee_no', 'like', $like);
            });
        }

        if ($request->boolean('all')) {
            return response()->json($query->get());
        }

        $perPage = (int) $request->query('per_page', 10);
        if ($perPage <= 0) {
            $perPage = 10;
        }

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->storeRules);

        $employeeData = Arr::only($validated, ['birthdate', 'sex', 'employee_no', 'date_hired', 'employee_type']);
        $userData = Arr::except($validated, ['birthdate', 'sex', 'employee_no', 'date_hired', 'employee_type']);
        $userData['status'] = 1;

        $record = DB::connection('user')->transaction(function () use ($userData, $employeeData) {
            $user = UserAccount::query()->create($userData);

            if ($this->hasEmployeePayload($employeeData)) {
                Employee::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    $employeeData
                );
            }

            return $user;
        });

        $record->refresh();
        $record->load('employee');

        return response()->json($this->appendEmployeeFields($record), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $record = $this->findRecord($id);
        $validated = $request->validate($this->resolveUpdateRules());

        $employeeData = Arr::only($validated, ['birthdate', 'sex', 'employee_no', 'date_hired', 'employee_type']);
        $userData = Arr::except($validated, ['birthdate', 'sex', 'employee_no', 'date_hired', 'employee_type']);

        DB::connection('user')->transaction(function () use ($record, $userData, $employeeData) {
            if (! empty($userData)) {
                $record->fill($userData);
                $record->save();
            }

            if ($this->hasEmployeePayload($employeeData)) {
                Employee::query()->updateOrCreate(
                    ['user_id' => $record->id],
                    $employeeData
                );
            }
        });

        $record->refresh();
        $record->load('employee');

        return response()->json($this->appendEmployeeFields($record));
    }

    public function resetPassword(int $id): JsonResponse
    {
        $user = UserAccount::query()->findOrFail($id);
        $user->password = '123456';
        $user->save();

        return response()->json([
            'message' => 'Password reset to 123456',
        ]);
    }

    protected function hasEmployeePayload(array $employeeData): bool
    {
        foreach ($employeeData as $value) {
            if ($value !== null && $value !== '') {
                return true;
            }
        }

        return false;
    }

    protected function appendEmployeeFields(UserAccount $user): UserAccount
    {
        $employee = $user->employee;

        $user->setAttribute('birthdate', $employee->birthdate ?? null);
        $user->setAttribute('sex', $employee->sex ?? null);
        $user->setAttribute('employee_no', $employee->employee_no ?? null);
        $user->setAttribute('date_hired', $employee->date_hired ?? null);
        $user->setAttribute('employee_type', $employee->employee_type ?? null);
        unset($user->employee);

        return $user;
    }
}
