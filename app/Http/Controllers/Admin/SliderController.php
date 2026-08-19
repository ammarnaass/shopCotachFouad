<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content\Slide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class SliderController extends Controller
{
    private const IMAGE_MAX_KB = 2048;

    private const IMAGE_MIMES = 'jpeg,jpg,png,webp';

    private const SLIDER_WIDTH = 1920;

    private const SLIDER_HEIGHT = 600;

    private const MOBILE_SLIDER_WIDTH = 1080;

    private const MOBILE_SLIDER_HEIGHT = 1200;

    private const CACHE_KEY = 'home.active_sliders';

    public function index(): View
    {
        $slides = Slide::ordered()->get();

        return view('admin.slider.index', compact('slides'));
    }

    public function create(): View
    {
        return view('admin.slider.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
            'badge' => 'nullable|string|max:50',
            'image_file' => 'nullable|file|mimes:'.self::IMAGE_MIMES.'|max:'.self::IMAGE_MAX_KB,
            'image' => 'nullable|string|max:500',
            'mobile_image_file' => 'nullable|file|mimes:'.self::IMAGE_MIMES.'|max:'.self::IMAGE_MAX_KB,
            'mobile_image' => 'nullable|string|max:500',
            'link' => 'nullable|url|max:500',
            'btn_text' => 'nullable|string|max:100',
            'button_target' => 'nullable|in:_same,_blank',
            'animation_effect' => 'nullable|in:fade,slide-left,slide-right,zoom,flip',
            'entrance_effect' => 'nullable|in:none,fade-up,fade-down,fade-left,fade-right,zoom',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        if ($request->hasFile('image_file')) {
            $data['image'] = $this->uploadImage($request->file('image_file'), 'slides/desktop');
        }
        if ($request->hasFile('mobile_image_file')) {
            $data['mobile_image'] = $this->uploadImage($request->file('mobile_image_file'), 'slides/mobile');
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['button_target'] = $data['button_target'] ?? '_same';
        $data['animation_effect'] = $data['animation_effect'] ?? site('slider_default_animation', 'fade');
        $data['entrance_effect'] = $data['entrance_effect'] ?? site('slider_default_entrance', 'fade-up');

        Slide::create($data);

        $this->invalidateCache();

        return redirect()->route('admin.slider.index')->with('success', __t('admin.slider.slide_created'));
    }

    public function edit(Slide $slider): View
    {
        return view('admin.slider.edit', ['slide' => $slider]);
    }

    public function update(Request $request, Slide $slider): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
            'badge' => 'nullable|string|max:50',
            'image_file' => 'nullable|file|mimes:'.self::IMAGE_MIMES.'|max:'.self::IMAGE_MAX_KB,
            'image' => 'nullable|string|max:500',
            'mobile_image_file' => 'nullable|file|mimes:'.self::IMAGE_MIMES.'|max:'.self::IMAGE_MAX_KB,
            'mobile_image' => 'nullable|string|max:500',
            'link' => 'nullable|url|max:500',
            'btn_text' => 'nullable|string|max:100',
            'button_target' => 'nullable|in:_same,_blank',
            'animation_effect' => 'nullable|in:fade,slide-left,slide-right,zoom,flip',
            'entrance_effect' => 'nullable|in:none,fade-up,fade-down,fade-left,fade-right,zoom',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        if ($request->hasFile('image_file')) {
            $data['image'] = $this->uploadImage($request->file('image_file'), 'slides/desktop', $slider->image);
        } elseif (empty($data['image'])) {
            unset($data['image']);
        }

        if ($request->hasFile('mobile_image_file')) {
            $data['mobile_image'] = $this->uploadImage($request->file('mobile_image_file'), 'slides/mobile', $slider->mobile_image);
        } elseif (empty($data['mobile_image'])) {
            unset($data['mobile_image']);
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['button_target'] = $data['button_target'] ?? '_same';
        $data['animation_effect'] = $data['animation_effect'] ?? site('slider_default_animation', 'fade');
        $data['entrance_effect'] = $data['entrance_effect'] ?? site('slider_default_entrance', 'fade-up');

        $slider->update($data);

        $this->invalidateCache();

        return redirect()->route('admin.slider.index')->with('success', __t('admin.slider.slide_updated'));
    }

    public function destroy(Slide $slider): RedirectResponse
    {
        if ($slider->image && ! preg_match('#^https?://#i', $slider->image) && Storage::disk('public')->exists($slider->image)) {
            Storage::disk('public')->delete($slider->image);
        }
        if ($slider->mobile_image && ! preg_match('#^https?://#i', $slider->mobile_image) && Storage::disk('public')->exists($slider->mobile_image)) {
            Storage::disk('public')->delete($slider->mobile_image);
        }
        $slider->delete();

        $this->invalidateCache();

        return redirect()->route('admin.slider.index')->with('success', __t('admin.slider.slide_deleted'));
    }

    public function toggleActive(Slide $slider): RedirectResponse
    {
        $slider->update(['is_active' => ! $slider->is_active]);
        $this->invalidateCache();

        return back()->with('success', __t('admin.slider.slide_updated'));
    }

    public function reorder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:slides,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($data['items'] as $item) {
            Slide::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        $this->invalidateCache();

        return response()->json(['success' => true, 'message' => __t('admin.slider.reorder_success')]);
    }

    private function invalidateCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function uploadImage($file, string $folder, ?string $oldPath = null): ?string
    {
        if (! $file || ! $file->isValid()) {
            return null;
        }
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $filename = $folder.'/'.Str::random(20).'.'.$ext;
        $stored = $file->storeAs(dirname($filename), basename($filename), 'public');
        if ($oldPath && ! preg_match('#^https?://#i', $oldPath) && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
        if ($stored) {
            $this->processImage(Storage::disk('public')->path($filename), $folder);
        }

        return $filename;
    }

    private function processImage(string $path, string $folder): void
    {
        try {
            $manager = new ImageManager(new Driver);
            $image = $manager->read($path);
            $isMobile = str_contains($folder, 'mobile');
            $maxWidth = $isMobile ? self::MOBILE_SLIDER_WIDTH : self::SLIDER_WIDTH;
            $maxHeight = $isMobile ? self::MOBILE_SLIDER_HEIGHT : self::SLIDER_HEIGHT;

            if ($image->width() >= $maxWidth || $image->height() >= $maxHeight) {
                $image->cover($maxWidth, $maxHeight);
                $image->save();
            }
        } catch (\Throwable $e) {
            logger()->warning('Slider image processing failed: '.$e->getMessage());
        }
    }
}
