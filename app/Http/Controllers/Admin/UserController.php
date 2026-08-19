<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function create(): RedirectResponse
    {
        // UserController doesn't have a dedicated create form — users self-register.
        return redirect()->route('admin.users.index')->with('error', 'لا يمكن إنشاء مستخدم من هنا. المستخدمون الجدد يسجلون عبر الموقع.');
    }

    public function index(Request $request): View
    {
        $query = User::query();
        if ($request->role) {
            $query->where('role', $request->role);
        }
        $users = $query->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load('orders', 'addresses');

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|unique:users,phone,'.$user->id,
            'role' => 'required|in:admin,manager,customer',
            'status' => 'required|in:active,inactive,banned',
            'password' => 'nullable|string|min:6',
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'تم التحديث');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك حذف حسابك');
        }
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'تم الحذف');
    }
}
