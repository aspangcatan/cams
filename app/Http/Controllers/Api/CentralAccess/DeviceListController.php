<?php

namespace App\Http\Controllers\Api\CentralAccess;

use App\Http\Controllers\Api\CrudController;
use App\Models\CentralAccess\DeviceList;
use App\Models\TdhUser\UserAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeviceListController extends CrudController
{
    protected string $modelClass = DeviceList::class;

    protected array $storeRules = [
        'userid' => 'required|integer',
        'android_id' => 'required|string|max:255',
    ];

    public function approve(int $id): JsonResponse
    {
        DB::transaction(function () use ($id) {
            $device = DeviceList::query()->findOrFail($id);
            $user = UserAccount::query()->findOrFail($device->userid);

            $user->android_id = $device->android_id;
            $user->save();

            $device->delete();
        });

        return response()->json([
            'message' => 'Device approved and applied to user profile.',
        ]);
    }
}
