<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(Request $request): View
    {
        $query = Tag::withCount('products');

        if ($request->search) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $tags = $query->latest()->paginate(20);

        return view('admin.tags.index', compact('tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:tags,name',
        ], [
            'name.required' => 'اسم الوسم مطلوب',
            'name.unique' => 'هذا الوسم موجود مسبقاً',
        ]);

        Tag::create($data);

        return redirect()->route('admin.tags.index')->with('success', 'تم إضافة الوسم بنجاح');
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:tags,name,'.$tag->id,
        ], [
            'name.required' => 'اسم الوسم مطلوب',
            'name.unique' => 'هذا الوسم موجود مسبقاً',
        ]);

        $tag->update($data);

        return redirect()->route('admin.tags.index')->with('success', 'تم تحديث الوسم بنجاح');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()->route('admin.tags.index')->with('success', 'تم حذف الوسم بنجاح');
    }
}
