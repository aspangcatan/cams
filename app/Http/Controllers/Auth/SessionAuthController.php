<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CentralAccess\AccessSystem;
use App\Models\TdhUser\UserAccount;
use App\Models\TdhUser\UserPrivilege;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SessionAuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('auth_user_id')) {
            return redirect('/');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        $user = UserAccount::query()
            ->where('username', $validated['username'])
            ->first();

        if (! $user || ! $user->password || ! Hash::check($validated['password'], $user->password)) {
            return back()
                ->withErrors(['username' => 'Invalid username or password.'])
                ->onlyInput('username');
        }

        if (! $this->hasCamsAdminAccess((int) $user->id)) {
            return back()
                ->withErrors(['username' => 'Access denied. Only CAMS admins are authorized.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();
        $request->session()->put('auth_user_id', $user->id);

        return redirect('/');
    }

    public function dashboard(Request $request): View
    {
        $user = UserAccount::query()->findOrFail((int) $request->session()->get('auth_user_id'));

        return view('admin.crud', [
            'authUser' => $user,
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('auth_user_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string|max:255',
            'new_password' => 'required|string|min:4|max:255|confirmed',
        ]);

        $user = UserAccount::query()->findOrFail((int) $request->session()->get('auth_user_id'));

        if (! $user->password || ! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->password = $validated['new_password'];
        $user->save();

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }

    private function hasCamsAdminAccess(int $userId): bool
    {
        $camsSystemCode = AccessSystem::query()
            ->whereRaw('LOWER(`system`) = ?', ['cams'])
            ->value('system') ?? 'cams';

        return UserPrivilege::query()
            ->where('user_id', $userId)
            ->whereRaw('LOWER(syscode) = ?', [strtolower((string) $camsSystemCode)])
            ->whereRaw('LOWER(level) LIKE ?', ['%admin%'])
            ->exists();
    }
}
