<?php

namespace App\Http\Middleware;

use App\Models\CentralAccess\AccessSystem;
use App\Models\TdhUser\UserPrivilege;
use Closure;
use Illuminate\Http\Request;

class EnsureSessionAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        $userId = (int) $request->session()->get('auth_user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        if (! $this->hasCamsAdminAccess($userId)) {
            $request->session()->forget('auth_user_id');
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        return $next($request);
    }

    private function hasCamsAdminAccess(int $userId): bool
    {
        $camsSystemCode = AccessSystem::query()
            ->whereRaw('LOWER(`system`) = ?', ['cams'])
            ->orWhereRaw('LOWER(description) LIKE ?', ['%cams%'])
            ->value('system') ?? 'cams';

        return UserPrivilege::query()
            ->where('user_id', $userId)
            ->whereRaw('LOWER(syscode) = ?', [strtolower((string) $camsSystemCode)])
            ->whereRaw('LOWER(level) LIKE ?', ['%admin%'])
            ->exists();
    }
}
