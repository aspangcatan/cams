<?php

namespace App\Http\Controllers\Api\TdhUser;

use App\Http\Controllers\Api\CrudController;
use App\Models\TdhUser\Designation;

class DesignationController extends CrudController
{
    protected string $modelClass = Designation::class;

    protected array $storeRules = [
        'description' => 'required|string|max:255',
    ];
}
