<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Inertia\Inertia;

class POSController extends Controller
{
    public function index()
    {
        $products = Product::with('variants.options')
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $p->price,
                'cogs' => $p->cogs,
                'stock' => $p->stock,
                'photo_url' => $p->photo_url,
                'category_id' => $p->category_id,
                'has_variants' => $p->variants->isNotEmpty(),
                'variants' => $p->variants->map(fn ($v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'options' => $v->options->map(fn ($o) => [
                        'id' => $o->id,
                        'label' => $o->label,
                        'price_modifier' => $o->price_modifier,
                        'cogs_modifier' => $o->cogs_modifier,
                    ]),
                ]),
            ]);

        $categories = Category::orderBy('name')->get(['id', 'name']);

        return Inertia::render('POS/Index', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
