<?php

namespace App\Modules\Catalog\Http\Controllers\Admin;

use App\Actions\Product\CreateProduct;
use App\Actions\Product\UpdateProduct;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductImage;
use App\Modules\Shipping\Models\ShippingCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public const IMAGE_MAX_SIZE_KB = 2048;

    public const IMAGE_MAX_FILES = 10;

    public const IMAGE_MIMES = 'jpeg,jpg,png,webp,gif';

    public const IMAGE_RECOMMENDED_W = 800;

    public const IMAGE_RECOMMENDED_H = 800;

    public const IMAGE_MIN_WIDTH = 300;

    public const IMAGE_MIN_HEIGHT = 300;

    public function index(Request $request): View
    {
        $query = Product::with('category', 'primaryImage');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::where('status', 'active')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::where('status', 'active')->get();
        $shippingCompanies = ShippingCompany::where('status', 'active')->orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'shippingCompanies'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = app(CreateProduct::class)->execute(
            $request->validated(),
            $request->file('images'),
            $request->input('options'),
            $request->input('custom_fields'),
            $request->input('product_shipping_rules'),
        );

        return redirect()->route('admin.products.gallery', $product)
            ->with('success', 'تم إضافة المنتج. يمكنك إضافة المزيد من الصور.');
    }

    public function show(Product $product): View
    {
        $product->load('images', 'options.values', 'variants', 'category');

        return view('admin.products.show', compact('product'));
    }

    public function gallery(Product $product): View
    {
        $product->load('images');

        return view('admin.products.gallery', compact('product'));
    }

    public function uploadImages(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'images' => 'required|array|min:1|max:'.self::IMAGE_MAX_FILES,
            'images.*' => 'image|mimes:'.self::IMAGE_MIMES.'|max:'.self::IMAGE_MAX_SIZE_KB,
            'primary' => 'nullable|integer',
        ]);

        $stored = $this->storeImages($request->file('images'), $product);

        if ($request->filled('primary') && in_array((int) $request->primary, $stored)) {
            ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
            ProductImage::where('id', (int) $request->primary)->update(['is_primary' => true]);
        }

        return redirect()->route('admin.products.gallery', $product)
            ->with('success', 'تم رفع '.count($stored).' صورة بنجاح');
    }

    public function setPrimaryImage(Product $product, ProductImage $image): RedirectResponse
    {
        if ($image->product_id !== $product->id) {
            abort(404);
        }
        DB::transaction(function () use ($product, $image) {
            ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
            $image->update(['is_primary' => true]);
        });

        return back()->with('success', 'تم تعيين الصورة كرئيسية');
    }

    public function updateImage(Request $request, Product $product, ProductImage $image): RedirectResponse
    {
        if ($image->product_id !== $product->id) {
            abort(404);
        }

        $data = $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        $image->update($data);

        return back()->with('success', 'تم تحديث الصورة');
    }

    public function destroyImage(Product $product, ProductImage $image): RedirectResponse
    {
        if ($image->product_id !== $product->id) {
            abort(404);
        }

        DB::transaction(function () use ($image, $product) {
            if ($image->image && Storage::disk('public')->exists($image->image)) {
                Storage::disk('public')->delete($image->image);
            }
            $wasPrimary = $image->is_primary;
            $image->delete();

            if ($wasPrimary) {
                $next = ProductImage::where('product_id', $product->id)
                    ->orderBy('order')->orderBy('id')
                    ->first();
                if ($next) {
                    $next->update(['is_primary' => true]);
                }
            }
        });

        return back()->with('success', 'تم حذف الصورة');
    }

    public function edit(Product $product): View
    {
        $categories = Category::where('status', 'active')->get();
        $shippingCompanies = ShippingCompany::where('status', 'active')->orderBy('name')->get();
        $product->load('images', 'options.values', 'variants', 'customFields');

        return view('admin.products.edit', compact('product', 'categories', 'shippingCompanies'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product = app(UpdateProduct::class)->execute(
            $product,
            $request->validated(),
            $request->input('options'),
            $request->input('custom_fields'),
            $request->input('product_shipping_rules'),
        );

        return redirect()->route('admin.products.gallery', $product)
            ->with('success', 'تم تحديث المنتج');
    }

    public function destroy(Product $product): RedirectResponse
    {
        foreach ($product->images as $img) {
            if ($img->image && Storage::disk('public')->exists($img->image)) {
                Storage::disk('public')->delete($img->image);
            }
        }
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'تم حذف المنتج');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete,feature,unfeature',
            'product_ids' => 'required|array|min:1',
        ]);

        $action = $request->action;
        $ids = $request->product_ids;

        match ($action) {
            'activate' => Product::whereIn('id', $ids)->update(['status' => 'active']),
            'deactivate' => Product::whereIn('id', $ids)->update(['status' => 'inactive']),
            'delete' => Product::whereIn('id', $ids)->each(function ($product) {
                foreach ($product->images as $img) {
                    if ($img->image && Storage::disk('public')->exists($img->image)) {
                        Storage::disk('public')->delete($img->image);
                    }
                }
                $product->delete();
            }),
            'feature' => Product::whereIn('id', $ids)->update(['featured' => true]),
            'unfeature' => Product::whereIn('id', $ids)->update(['featured' => false]),
        };

        $msg = match ($action) {
            'activate' => 'تم تفعيل '.count($ids).' منتج',
            'deactivate' => 'تم تعطيل '.count($ids).' منتج',
            'delete' => 'تم حذف '.count($ids).' منتج',
            'feature' => 'تم تمييز '.count($ids).' منتج',
            'unfeature' => 'تم إلغاء تمييز '.count($ids).' منتج',
        };

        return redirect()->route('admin.products.index')->with('success', $msg);
    }

    public function export(Request $request)
    {
        $query = Product::with('category');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $products = $query->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products-'.date('Y-m-d').'.csv"',
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'الاسم', 'SKU', 'السعر', 'سعر الخصم', 'المخزون', 'التصنيف', 'الحالة', 'مميز', 'الوزن']);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->sku,
                    $product->price,
                    $product->sale_price ?? '',
                    $product->stock,
                    $product->category?->name ?? '',
                    $product->status,
                    $product->featured ? 'نعم' : 'لا',
                    $product->weight ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function storeImages(array $files, Product $product): array
    {
        $hasPrimary = ProductImage::where('product_id', $product->id)
            ->where('is_primary', true)->exists();
        $currentOrder = (int) ProductImage::where('product_id', $product->id)
            ->max('order') + 1;

        $created = [];
        foreach ($files as $i => $file) {
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $filename = 'products/'.$product->id.'/'.Str::random(20).'.'.$ext;
            $file->storeAs(dirname($filename), basename($filename), 'public');

            $img = ProductImage::create([
                'product_id' => $product->id,
                'image' => $filename,
                'is_primary' => ! $hasPrimary && $i === 0,
                'order' => $currentOrder++,
            ]);
            $created[] = $img->id;
        }

        return $created;
    }
}
