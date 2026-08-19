<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content\FooterLink;
use App\Models\Content\FooterSection;
use App\Models\Content\FooterSocial;
use App\Services\FooterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FooterController extends Controller
{
    public function __construct(private FooterService $footerService) {}

    public function index(): View
    {
        $sections = FooterSection::with('links')->ordered()->get();
        $socials  = FooterSocial::ordered()->get();

        return view('admin.footer.index', compact('sections', 'socials'));
    }

    public function storeSection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'type'        => 'required|in:links,categories,custom_html,contact,social,store_info',
            'custom_html' => 'nullable|string',
            'sort_order'  => 'nullable|integer',
            'status'      => 'nullable|boolean',
        ]);

        $validated['status'] = $request->boolean('status', true);
        FooterSection::create($validated);
        $this->footerService->flush();

        return back()->with('success', 'تم إضافة القسم بنجاح');
    }

    public function updateSection(Request $request, FooterSection $section): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'type'        => 'required|in:links,categories,custom_html,contact,social,store_info',
            'custom_html' => 'nullable|string',
            'sort_order'  => 'nullable|integer',
            'status'      => 'nullable|boolean',
        ]);

        $validated['status'] = $request->boolean('status');
        $section->update($validated);
        $this->footerService->flush();

        return back()->with('success', 'تم تحديث القسم بنجاح');
    }

    public function destroySection(FooterSection $section): RedirectResponse
    {
        $section->delete();
        $this->footerService->flush();

        return back()->with('success', 'تم حذف القسم بنجاح');
    }

    public function storeLink(Request $request, FooterSection $section): RedirectResponse
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'url'        => 'required|string|max:500',
            'target'     => 'required|in:_self,_blank',
            'sort_order' => 'nullable|integer',
            'status'     => 'nullable|boolean',
        ]);

        $validated['status'] = $request->boolean('status', true);
        $section->links()->create($validated);
        $this->footerService->flush();

        return back()->with('success', 'تم إضافة الرابط بنجاح');
    }

    public function updateLink(Request $request, FooterLink $link): RedirectResponse
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'url'        => 'required|string|max:500',
            'target'     => 'required|in:_self,_blank',
            'sort_order' => 'nullable|integer',
            'status'     => 'nullable|boolean',
        ]);

        $validated['status'] = $request->boolean('status');
        $link->update($validated);
        $this->footerService->flush();

        return back()->with('success', 'تم تحديث الرابط بنجاح');
    }

    public function destroyLink(FooterLink $link): RedirectResponse
    {
        $link->delete();
        $this->footerService->flush();

        return back()->with('success', 'تم حذف الرابط بنجاح');
    }

    public function storeSocial(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'platform'   => 'required|in:facebook,instagram,tiktok,youtube,whatsapp,telegram,snapchat,twitter|unique:footer_socials,platform',
            'url'        => 'required|string|max:500',
            'sort_order' => 'nullable|integer',
            'status'     => 'nullable|boolean',
        ]);

        $validated['status'] = $request->boolean('status', true);
        FooterSocial::create($validated);
        $this->footerService->flush();

        return back()->with('success', 'تم إضافة رابط التواصل بنجاح');
    }

    public function updateSocial(Request $request, FooterSocial $social): RedirectResponse
    {
        $validated = $request->validate([
            'url'        => 'required|string|max:500',
            'sort_order' => 'nullable|integer',
            'status'     => 'nullable|boolean',
        ]);

        $validated['status'] = $request->boolean('status');
        $social->update($validated);
        $this->footerService->flush();

        return back()->with('success', 'تم تحديث رابط التواصل بنجاح');
    }

    public function destroySocial(FooterSocial $social): RedirectResponse
    {
        $social->delete();
        $this->footerService->flush();

        return back()->with('success', 'تم حذف رابط التواصل بنجاح');
    }

    public function reorderSections(Request $request): RedirectResponse
    {
        $orders = $request->input('orders', []);
        foreach ($orders as $id => $order) {
            FooterSection::where('id', $id)->update(['sort_order' => (int) $order]);
        }
        $this->footerService->flush();

        return back()->with('success', 'تم إعادة الترتيب بنجاح');
    }
}
