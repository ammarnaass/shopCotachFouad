<?php

namespace App\Modules\CMS\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\CMS\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        $pages = Page::latest()->paginate(20);

        return view('admin.pages.index', compact('pages'));
    }

    public function create(): View
    {
        return view('admin.pages.form', ['page' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePage($request);
        Page::create($data);

        return redirect()->route('admin.pages.index')->with('success', 'تم إنشاء الصفحة بنجاح');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.form', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $this->validatePage($request, $page->id);
        $page->update($data);

        return redirect()->route('admin.pages.index')->with('success', 'تم تحديث الصفحة بنجاح');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'تم حذف الصفحة بنجاح');
    }

    private function validatePage(Request $request, ?int $ignoreId = null): array
    {
        $uniqueRule = $ignoreId ? 'unique:pages,slug,'.$ignoreId : 'unique:pages,slug';

        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|'.$uniqueRule,
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'intro' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'title.required' => 'عنوان الصفحة مطلوب',
            'slug.required' => 'الرابط المختصر مطلوب',
            'slug.unique' => 'هذا الرابط مستخدم مسبقاً',
        ]);
    }
}
