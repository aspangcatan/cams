<?php

namespace App\Http\Controllers\Api\CentralAccess;

use App\Http\Controllers\Api\CrudController;
use App\Models\CentralAccess\AccessRight;
use App\Models\CentralAccess\AccessSystem;
use App\Models\TdhUser\UserAccount;
use App\Models\TdhUser\UserPrivilege;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessSystemController extends CrudController
{
    protected string $modelClass = AccessSystem::class;

    protected array $storeRules = [
        'system' => 'required|string|max:255',
        'description' => 'nullable|string|max:255',
    ];

    public function assignments(Request $request, int $system): JsonResponse
    {
        $systemModel = AccessSystem::query()->findOrFail($system);
        $roles = AccessRight::query()
            ->where('system_id', $systemModel->id)
            ->orderBy('role')
            ->get(['id', 'role', 'role_description']);

        $perPage = (int) $request->query('per_page', 10);
        if ($perPage <= 0) {
            $perPage = 10;
        }

        $privilegeQuery = UserPrivilege::query()
            ->where('syscode', $systemModel->system)
            ->orderByDesc('id');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $like = '%' . $search . '%';
            $matchedUserIds = UserAccount::query()
                ->where(function ($builder) use ($like) {
                    $builder->where('fname', 'like', $like)
                        ->orWhere('mname', 'like', $like)
                        ->orWhere('lname', 'like', $like)
                        ->orWhereRaw("CONCAT_WS(' ', fname, mname, lname, suffix) LIKE ?", [$like]);
                })
                ->pluck('id');

            if ($matchedUserIds->isEmpty()) {
                $privilegeQuery->whereRaw('1 = 0');
            } else {
                $privilegeQuery->whereIn('user_id', $matchedUserIds);
            }
        }

        $privileges = $privilegeQuery->paginate($perPage);

        $userIds = $privileges->getCollection()->pluck('user_id')->filter()->unique()->values();
        $users = UserAccount::query()
            ->whereIn('id', $userIds)
            ->get(['id', 'fname', 'mname', 'lname', 'suffix'])
            ->keyBy('id');

        $mapped = $privileges->getCollection()->map(function (UserPrivilege $privilege) use ($users) {
            $user = $users->get($privilege->user_id);
            $name = $this->resolveUserDisplayName($user, $privilege->user_id);

            return [
                'id' => $privilege->id,
                'user_id' => $privilege->user_id,
                'user_name' => $name,
                'level' => $privilege->level,
            ];
        })->values();

        $privileges->setCollection($mapped);

        return response()->json([
            'system' => $systemModel->only(['id', 'system', 'description']),
            'roles' => $roles,
            'assignments' => $privileges,
        ]);
    }

    public function storeAssignments(Request $request, int $system): JsonResponse
    {
        $systemModel = AccessSystem::query()->findOrFail($system);
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer',
            'level' => 'required|string|max:255',
        ]);

        $existsRole = AccessRight::query()
            ->where('system_id', $systemModel->id)
            ->where('role', $validated['level'])
            ->exists();
        if (! $existsRole) {
            return response()->json(['message' => 'Selected role is invalid for this system.'], 422);
        }

        $userCount = UserAccount::query()
            ->whereIn('id', $validated['user_ids'])
            ->count();
        if ($userCount !== count(array_unique($validated['user_ids']))) {
            return response()->json(['message' => 'One or more users are invalid.'], 422);
        }

        DB::connection('user')->transaction(function () use ($validated, $systemModel) {
            foreach (array_unique($validated['user_ids']) as $userId) {
                UserPrivilege::query()->updateOrCreate(
                    [
                        'user_id' => $userId,
                        'syscode' => $systemModel->system,
                    ],
                    [
                        'level' => $validated['level'],
                    ]
                );
            }
        });

        return response()->json(['message' => 'Access assigned successfully.'], 201);
    }

    public function updateAssignment(Request $request, int $system, int $assignment): JsonResponse
    {
        $systemModel = AccessSystem::query()->findOrFail($system);
        $validated = $request->validate([
            'level' => 'required|string|max:255',
        ]);

        $existsRole = AccessRight::query()
            ->where('system_id', $systemModel->id)
            ->where('role', $validated['level'])
            ->exists();
        if (! $existsRole) {
            return response()->json(['message' => 'Selected role is invalid for this system.'], 422);
        }

        $privilege = UserPrivilege::query()
            ->where('id', $assignment)
            ->where('syscode', $systemModel->system)
            ->firstOrFail();
        $privilege->level = $validated['level'];
        $privilege->save();

        return response()->json(['message' => 'Access updated successfully.']);
    }

    public function destroyAssignment(int $system, int $assignment): JsonResponse
    {
        $systemModel = AccessSystem::query()->findOrFail($system);
        $privilege = UserPrivilege::query()
            ->where('id', $assignment)
            ->where('syscode', $systemModel->system)
            ->firstOrFail();
        $privilege->delete();

        return response()->json(null, 204);
    }

    private function resolveUserDisplayName(?UserAccount $user, ?int $fallbackId): string
    {
        if (! $user) {
            return $fallbackId ? "User #{$fallbackId}" : 'Unknown User';
        }

        $parts = array_filter([
            trim((string) $user->fname),
            trim((string) $user->mname),
            trim((string) $user->lname),
            trim((string) $user->suffix),
        ]);

        if (empty($parts)) {
            return "User #{$user->id}";
        }

        return implode(' ', $parts);
    }
}
