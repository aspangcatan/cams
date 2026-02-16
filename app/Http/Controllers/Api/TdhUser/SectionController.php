<?php

namespace App\Http\Controllers\Api\TdhUser;

use App\Http\Controllers\Api\CrudController;
use App\Models\TdhUser\Section;

class SectionController extends CrudController
{
    protected string $modelClass = Section::class;

    protected array $storeRules = [
        'description' => 'required|string|max:255',
        'head' => 'nullable|integer',
        'code' => 'nullable|string|max:100',
        'subsection' => 'nullable|integer',
    ];
}
