<?php

namespace App\Http\Controllers;

use App\Http\Requests\Web\AccountPasswordRequest;
use App\Models\Shipping\ShippingAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $user = Auth::user()->load('addresses', 'orders', 'roles');
        $countries = config('ecommerce.countries', []);

        return view('frontend.account.index', compact('user', 'countries'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'string', Rule::unique('users')->ignore($user->id)],
            'country_code' => 'required|string|size:2',
            'state_code' => 'nullable|string|max:5',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
        ], [], [
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'phone' => 'الهاتف',
            'country_code' => 'الدولة',
            'avatar' => 'الصورة الشخصية',
        ]);

        $countries = config('ecommerce.countries', []);
        $dial = $countries[$data['country_code']]['dial_code'] ?? '';
        $data['phone'] = str_starts_with($data['phone'], '+') ? $data['phone'] : ($dial.$data['phone']);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } elseif ($request->boolean('remove_avatar')) {
            if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = null;
        }

        $user->update($data);

        return back()->with('success', 'تم تحديث البيانات الشخصية بنجاح');
    }

    public function updatePassword(AccountPasswordRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح');
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'country_code' => 'required|string|size:2',
            'state_code' => 'nullable|string|max:5',
            'city' => 'required|string',
            'district' => 'nullable|string',
            'address' => 'required|string',
            'zip' => 'nullable|string',
            'is_default' => 'boolean',
        ]);

        $data['user_id'] = Auth::id();

        if (! empty($data['is_default'])) {
            ShippingAddress::where('user_id', $data['user_id'])->update(['is_default' => false]);
        }

        ShippingAddress::create($data);

        return back()->with('success', 'تم إضافة العنوان بنجاح');
    }

    public function setDefaultAddress(ShippingAddress $address): RedirectResponse
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        ShippingAddress::where('user_id', Auth::id())->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', 'تم تعيين العنوان كافتراضي');
    }

    public function destroyAddress(ShippingAddress $address): RedirectResponse
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }
        $address->delete();

        return back()->with('success', 'تم حذف العنوان');
    }
}
