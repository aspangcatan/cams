<?php

namespace App\Http\Controllers\Api\TdhUser;

use App\Http\Controllers\Api\CrudController;
use App\Models\TdhUser\Division;

class DivisionController extends CrudController
{
    protected string $modelClass = Division::class;

    protected array $storeRules = [
        'description' => 'required|string|max:255',
        'head' => 'nullable|integer',
        'code' => 'nullable|string|max:100',
    ];
}
