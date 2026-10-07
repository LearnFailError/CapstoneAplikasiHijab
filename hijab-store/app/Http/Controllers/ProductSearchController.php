<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductSearchController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'material' => ['nullable', 'string', Rule::in(Product::query()->whereNotNull('material')->distinct()->pluck('material')->all())],
            'color' => ['nullable', 'string', Rule::in(Product::query()->whereNotNull('color')->distinct()->pluck('color')->all())],
            'min_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999', 'gte:min_price'],
        ]);

        $filters = [];

        if (isset($validated['category_id'])) {
            $filters[] = 'category_id:='.$validated['category_id'];
        }

        foreach (['material', 'color'] as $field) {
            if (isset($validated[$field])) {
                $value = str_replace(['\\', '`'], ['\\\\', '\\`'], $validated[$field]);
                $filters[] = $field.':=`'.$value.'`';
            }
        }

        if (isset($validated['min_price'])) {
            $filters[] = 'price:>='.number_format((float) $validated['min_price'], 2, '.', '');
        }

        if (isset($validated['max_price'])) {
            $filters[] = 'price:<='.number_format((float) $validated['max_price'], 2, '.', '');
        }

        $builder = Product::search($validated['q'] ?? '*');
        $filters[] = 'is_active:=1';
        $builder->options([
            'num_typos' => 2,
            'typo_tokens_threshold' => 1,
            'filter_by' => implode(' && ', $filters),
            'facet_by' => 'category_id,material,color',
        ]);

        try {
            $results = $builder
                ->query(fn ($query) => $query->with('category'))
                ->paginate(12)
                ->withQueryString();
            $facets = $builder->raw()['facet_counts'] ?? [];
        } catch (ConnectException $exception) {
            Log::warning('Typesense is unavailable; product search could not run.', [
                'exception' => $exception::class,
            ]);

            return response()->view('products.unavailable', status: 503);
        }

        $categories = Category::query()->orderBy('name')->get();
        $materials = Product::query()->whereNotNull('material')->distinct()->orderBy('material')->pluck('material');
        $colors = Product::query()->whereNotNull('color')->distinct()->orderBy('color')->pluck('color');

        return view('products.search', [
            'results' => $results,
            'facets' => $facets,
            'categories' => $categories,
            'materials' => $materials,
            'colors' => $colors,
            'query' => $validated['q'] ?? '',
        ]);
    }
}
