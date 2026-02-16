<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class CrudController extends Controller
{
    protected string $modelClass;

    protected array $storeRules = [];

    protected array $updateRules = [];

    public function index(Request $request): JsonResponse
    {
        $modelClass = $this->modelClass;
        $query = $modelClass::query()->orderByDesc('id');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $like = '%' . $search . '%';
            $fillable = (new $modelClass())->getFillable();

            if (! empty($fillable)) {
                $query->where(function ($builder) use ($fillable, $like) {
                    foreach ($fillable as $column) {
                        $builder->orWhere($column, 'like', $like);
                    }
                });
            }
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
        $modelClass = $this->modelClass;
        $validated = $request->validate($this->storeRules);

        $record = $modelClass::query()->create($validated);

        return response()->json($record, 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->findRecord($id));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $record = $this->findRecord($id);
        $validated = $request->validate($this->resolveUpdateRules());

        $record->fill($validated);
        $record->save();

        return response()->json($record);
    }

    public function destroy(int $id): JsonResponse
    {
        $record = $this->findRecord($id);
        $record->delete();

        return response()->json(null, 204);
    }

    protected function findRecord(int $id): Model
    {
        $modelClass = $this->modelClass;

        return $modelClass::query()->findOrFail($id);
    }

    protected function resolveUpdateRules(): array
    {
        if (! empty($this->updateRules)) {
            return $this->updateRules;
        }

        $rules = [];

        foreach ($this->storeRules as $field => $rule) {
            if (! is_string($rule)) {
                $rules[$field] = $rule;
                continue;
            }

            $parts = array_filter(explode('|', $rule), fn (string $part) => $part !== 'required');
            array_unshift($parts, 'sometimes');
            $rules[$field] = implode('|', $parts);
        }

        return $rules;
    }
}
