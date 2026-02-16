<?php

namespace App\Http\Controllers\Api\TdhUser;

use App\Http\Controllers\Api\CrudController;
use App\Models\TdhUser\UserAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        'status' => 'nullable|string|max:50',
    ];

    public function index(Request $request): JsonResponse
    {
        $query = UserAccount::query()->orderByDesc('id');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($builder) use ($like) {
                $builder->where('fname', 'like', $like)
                    ->orWhere('mname', 'like', $like)
                    ->orWhere('lname', 'like', $like)
                    ->orWhereRaw("CONCAT_WS(' ', fname, mname, lname, suffix) LIKE ?", [$like]);
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

    public function resetPassword(int $id): JsonResponse
    {
        $user = UserAccount::query()->findOrFail($id);
        $user->password = '1234';
        $user->save();

        return response()->json([
            'message' => 'Password reset to 1234.',
        ]);
    }
}
