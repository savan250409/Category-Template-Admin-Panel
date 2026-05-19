<?php

namespace App\Http\Controllers;

use App\Models\Filter;
use App\Models\FilterCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterController extends Controller
{
    public function index(Request $request)
    {
        $perPage     = (int) $request->input('per_page', 10);
        $search      = $request->input('search', '');
        $categoryId  = $request->input('category_id');

        $query = Filter::with('category');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($categoryId) {
            $query->where('filter_category_id', $categoryId);
        }

        $filters = $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $categories = FilterCategory::orderBy('name')->get(['id', 'name']);

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('filter.filters.table', compact('filters'))->render(),
                'total' => $filters->total(),
            ]);
        }

        return view('filter.filters.index', compact('filters', 'categories', 'perPage', 'search', 'categoryId'));
    }

    public function create()
    {
        $categories = FilterCategory::orderBy('name')->get(['id', 'name']);
        return view('filter.filters.form', compact('categories'));
    }

    public function store(Request $request)
    {
        try {
            $data = $this->validateFilter($request);

            $minOrder = Filter::min('sort_order');
            $minOrder = is_null($minOrder) ? 0 : (int) $minOrder;

            $filter = new Filter();
            $filter->fill($data);
            $filter->is_premium = $request->has('is_premium') ? 1 : 0;
            $filter->status     = 1;
            $filter->sort_order = $minOrder - 1;
            $filter->save();

            return view('partials.history_redirect', ['fallback' => route('filter.filters.index'), 'message' => 'Filter created successfully!']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to save filter: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $filter     = Filter::findOrFail($id);
        $categories = FilterCategory::orderBy('name')->get(['id', 'name']);
        return view('filter.filters.form', compact('filter', 'categories'));
    }

    public function update(Request $request, $id)
    {
        try {
            $filter = Filter::findOrFail($id);
            $data   = $this->validateFilter($request, $filter->id);

            $filter->fill($data);
            $filter->is_premium = $request->has('is_premium') ? 1 : 0;
            $filter->save();

            return view('partials.history_redirect', ['fallback' => route('filter.filters.index'), 'message' => 'Filter updated successfully!']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->withInput()->with('error', $errors)->withErrors($e->errors());

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update filter: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $filter = Filter::findOrFail($id);
        $filter->delete();
        return redirect()->route('filter.filters.index')->with('success', 'Filter deleted successfully!');
    }

    public function togglePremium(Request $request)
    {
        $filter = Filter::findOrFail($request->id);
        $filter->is_premium = $request->is_premium ? 1 : 0;
        $filter->save();
        return response()->json(['success' => true, 'message' => 'Premium updated successfully!', 'is_premium' => $filter->is_premium]);
    }

    public function importForm()
    {
        return view('filter.filters.import');
    }

    public function importCsv(Request $request)
    {
        try {
            $request->validate([
                'csv' => 'required|file|mimes:csv,txt|max:10240',
            ], [
                'csv.mimes' => 'Please upload a .csv file.',
            ]);

            $path = $request->file('csv')->getRealPath();
            $fh   = fopen($path, 'r');
            if (!$fh) throw new \Exception('Unable to read CSV file.');

            $header = fgetcsv($fh);
            if (!$header) throw new \Exception('CSV is empty.');

            $map = $this->buildHeaderMap($header);
            if (!isset($map['category']) || !isset($map['name'])) {
                throw new \Exception('CSV must contain at least "category" and "filter_name" columns.');
            }

            $created = 0;
            $updated = 0;
            $skipped = 0;
            $errors  = [];
            $rowNum  = 1;

            DB::beginTransaction();

            while (($row = fgetcsv($fh)) !== false) {
                $rowNum++;
                if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) continue;

                $categoryName = trim((string) ($row[$map['category']] ?? ''));
                $filterName   = trim((string) ($row[$map['name']] ?? ''));

                if ($categoryName === '' || $filterName === '') {
                    $skipped++;
                    $errors[] = "Row {$rowNum}: missing category or filter name";
                    continue;
                }

                $category = FilterCategory::firstOrCreate(
                    ['name' => $categoryName],
                    ['status' => 1, 'sort_order' => 0]
                );

                $typeRaw   = strtolower(trim((string) ($row[$map['type'] ?? -1] ?? 'pro')));
                $isPremium = !in_array($typeRaw, ['free', '0', 'false', 'no'], true);

                $values = [
                    'filter_category_id' => $category->id,
                    'name'       => $filterName,
                    'is_premium' => $isPremium,
                    'saturation' => $this->num($row, $map, 'saturation', 1),
                    'brightness' => $this->num($row, $map, 'brightness', 0),
                    'contrast'   => $this->num($row, $map, 'contrast',   1),
                    'red'        => $this->num($row, $map, 'red',        1),
                    'green'      => $this->num($row, $map, 'green',      1),
                    'blue'       => $this->num($row, $map, 'blue',       1),
                    'status'     => 1,
                ];

                $existing = Filter::where('filter_category_id', $category->id)
                    ->where('name', $filterName)
                    ->first();

                if ($existing) {
                    $existing->fill($values)->save();
                    $updated++;
                } else {
                    $values['sort_order'] = (int) (Filter::min('sort_order') ?? 0) - 1;
                    Filter::create($values);
                    $created++;
                }
            }

            fclose($fh);
            DB::commit();

            $msg = "Import complete: {$created} created, {$updated} updated";
            if ($skipped > 0) $msg .= ", {$skipped} skipped";

            return view('partials.history_redirect', ['fallback' => route('filter.filters.index'), 'message' => $msg]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->map(fn($msgs) => $msgs[0])->values()->implode(' | ');
            return redirect()->back()->with('error', $errors);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    private function validateFilter(Request $request, $ignoreId = null): array
    {
        $unique = 'unique:filters,name,' . ($ignoreId ?? 'NULL') . ',id,filter_category_id,' . (int) $request->filter_category_id;

        return $request->validate([
            'filter_category_id' => 'required|exists:filter_categories,id',
            'name'               => 'required|string|max:255|' . $unique,
            'saturation'         => 'required|numeric',
            'brightness'         => 'required|numeric',
            'contrast'           => 'required|numeric',
            'red'                => 'required|numeric',
            'green'              => 'required|numeric',
            'blue'               => 'required|numeric',
        ]);
    }

    private function buildHeaderMap(array $header): array
    {
        $aliases = [
            'category'   => ['category', 'category_name', 'cat', 'filter_category'],
            'name'       => ['filter_name', 'name', 'filter'],
            'type'       => ['type', 'is_premium', 'premium'],
            'saturation' => ['saturation', 'sat', 's'],
            'brightness' => ['brightness', 'bright'],
            'contrast'   => ['contrast', 'con', 'c'],
            'red'        => ['red', 'r'],
            'green'      => ['green', 'g'],
            'blue'       => ['blue'],
        ];

        $normalized = array_map(function ($h) {
            $h = strtolower(trim((string) $h));
            $h = preg_replace('/[\s\-]+/', '_', $h);
            return trim($h, '_');
        }, $header);
        $map = [];

        foreach ($aliases as $key => $names) {
            foreach ($names as $candidate) {
                $idx = array_search($candidate, $normalized, true);
                if ($idx !== false) {
                    $map[$key] = $idx;
                    break;
                }
            }
        }

        return $map;
    }

    private function num(array $row, array $map, string $key, float $default): float
    {
        if (!isset($map[$key])) return $default;
        $val = $row[$map[$key]] ?? '';
        if ($val === '' || $val === null) return $default;
        return is_numeric($val) ? (float) $val : $default;
    }
}
