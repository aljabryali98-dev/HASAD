<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // فحص تفعيل الحساب
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'حسابك معطل حالياً من قِبل إدارة النظام. يرجى التواصل مع المدير المسؤول.',
            ]);
        }

        // مدير النظام المركزي يملك كامل الصلاحيات دائماً
        if ($user->isAdmin()) {
            return $next($request);
        }

        // فحص الصلاحية المطلوبة
        if (!$user->hasPermission($permission)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'عذراً، ليس لديك الصلاحية الكافية للوصول إلى هذا القسم أو تنفيذ هذا الإجراء.',
                ], 403);
            }

            return redirect()->route('dashboard')->with('error', 'عذراً، لا تملك الصلاحية الكافية للوصول إلى هذه الصفحة.');
        }

        return $next($request);
    }
}
