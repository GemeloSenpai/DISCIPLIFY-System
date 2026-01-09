<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session as SessionFacade; // Usamos la fachada



class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();
        
        // Llamada al método para limitar sesiones
        $this->limitarSesiones($user->id);

        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'maestro' => redirect()->route('maestro.dashboard'),
            'alumno' => redirect()->route('alumno.dashboard'),
            default => redirect('/'),
        };
    }

    /**
     * Método para limitar sesiones concurrentes
     */
    protected function limitarSesiones($userId): void
    {
        // Usamos el sistema de sesiones de Laravel
        $sessionKey = 'user_sessions_' . $userId;
        $maxSessions = 2;
        
        $sessions = SessionFacade::get($sessionKey, []);
        $sessions = array_slice($sessions, -($maxSessions - 1));
        $sessions[] = SessionFacade::getId();
        
        SessionFacade::put($sessionKey, $sessions);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}