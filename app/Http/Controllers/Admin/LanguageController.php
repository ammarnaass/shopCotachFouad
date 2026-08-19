<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Settings\Language;
use App\Models\Settings\Translation;
use App\Services\TranslationService;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    protected TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    public function index()
    {
        $languages = Language::ordered()->get();

        return view('admin.languages.index', compact('languages'));
    }

    public function edit(Language $language)
    {
        return view('admin.languages.edit', compact('language'));
    }

    public function update(Request $request, Language $language)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'name_en' => 'nullable|string|max:50',
            'native_name' => 'required|string|max:50',
            'flag' => 'nullable|string|max:10',
            'locale' => 'nullable|string|max:10',
            'direction' => 'required|in:rtl,ltr',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer|min:0',
            'date_format' => 'required|string|max:20',
            'time_format' => 'required|string|max:20',
            'decimal_separator' => 'required|string|max:5',
            'thousands_separator' => 'required|string|max:5',
            'currency_position' => 'required|in:before,after',
        ]);

        if ($data['is_default'] ?? false) {
            Language::where('is_default', true)->update(['is_default' => false]);
        }

        $language->update($data);
        $this->translationService->cacheFlush();

        return redirect()->route('admin.languages.index')->with('success', 'تم تحديث اللغة بنجاح');
    }

    public function toggleActive(Language $language)
    {
        $language->update(['is_active' => ! $language->is_active]);
        $this->translationService->cacheFlush();

        return back()->with('success', $language->is_active ? 'تم تفعيل اللغة' : 'تم إلغاء تفعيل اللغة');
    }

    public function setDefault(Language $language)
    {
        Language::where('is_default', true)->update(['is_default' => false]);
        $language->update(['is_default' => true, 'is_active' => true]);
        $this->translationService->cacheFlush();

        return back()->with('success', 'تم تعيين اللغة الافتراضية');
    }

    public function translations(Request $request)
    {
        $languages = Language::active()->ordered()->get();
        $selectedLang = $request->get('language_id');
        $selectedGroup = $request->get('group', 'instant_buy');

        $groups = Translation::select('group')->distinct()->pluck('group');

        $translations = collect();
        $language = null;

        if ($selectedLang) {
            $language = Language::find($selectedLang);
            $translations = Translation::where('language_id', $selectedLang)
                ->where('group', $selectedGroup)
                ->orderBy('key')
                ->get();
        }

        return view('admin.languages.translations', compact(
            'languages', 'selectedLang', 'selectedGroup', 'groups', 'translations', 'language'
        ));
    }

    public function updateTranslation(Request $request, Translation $translation)
    {
        $data = $request->validate([
            'value' => 'nullable|string',
        ]);

        $translation->update([
            'value' => $data['value'],
            'is_custom' => true,
        ]);

        $this->translationService->cacheFlush($translation->language->code);

        return back()->with('success', 'تم تحديث الترجمة');
    }

    public function createTranslation(Request $request)
    {
        $data = $request->validate([
            'language_id' => 'required|exists:languages,id',
            'group' => 'required|string|max:50',
            'key' => 'required|string|max:100',
            'value' => 'nullable|string',
        ]);

        Translation::create([
            'language_id' => $data['language_id'],
            'group' => $data['group'],
            'key' => $data['key'],
            'value' => $data['value'],
            'is_custom' => true,
        ]);

        $this->translationService->cacheFlush();

        return back()->with('success', 'تم إنشاء الترجمة');
    }

    public function deleteTranslation(Translation $translation)
    {
        $translation->delete();
        $this->translationService->cacheFlush();

        return back()->with('success', 'تم حذف الترجمة');
    }

    public function bulkUpdateTranslations(Request $request)
    {
        $data = $request->validate([
            'translations' => 'required|array',
            'translations.*.id' => 'required|exists:translations,id',
            'translations.*.value' => 'nullable|string',
        ]);

        foreach ($data['translations'] as $item) {
            Translation::where('id', $item['id'])->update([
                'value' => $item['value'],
                'is_custom' => true,
            ]);
        }

        $this->translationService->cacheFlush();

        return back()->with('success', 'تم تحديث جميع الترجمات');
    }

    public function settings()
    {
        $languages = Language::active()->ordered()->get();

        return view('admin.languages.settings', compact('languages'));
    }

    public function updateSettings(Request $request, Language $language)
    {
        $data = $request->validate([
            'date_format' => 'required|string|max:20',
            'time_format' => 'required|string|max:20',
            'decimal_separator' => 'required|string|max:5',
            'thousands_separator' => 'required|string|max:5',
            'currency_position' => 'required|in:before,after',
        ]);

        $language->update($data);
        $this->translationService->cacheFlush();

        return back()->with('success', 'تم حفظ إعدادات التنسيق للغة '.$language->name);
    }
}
