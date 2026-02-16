<?php

namespace App\Http\Controllers\Api\CentralAccess;

use App\Http\Controllers\Api\CrudController;
use App\Models\CentralAccess\AccessRight;

class AccessRightController extends CrudController
{
    protected string $modelClass = AccessRight::class;

    protected array $storeRules = [
        'system_id' => 'required|integer',
        'role' => 'required|string|max:255',
        'role_description' => 'nullable|string|max:255',
    ];
}
