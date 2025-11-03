<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->filled('remember');

        if (Auth::attempt($request->only('email', 'password'), $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            
            // Redirect based on user role
            return $this->redirectBasedOnRole($user, $request);
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * Redirect user based on their role after login.
     *
     * @param  \App\Models\User  $user
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectBasedOnRole($user, Request $request)
    {
        // Check if there's an intended URL (user was redirected to login from a protected route)
        $intended = $request->session()->pull('url.intended');
        
        if ($intended) {
            return redirect($intended);
        }

        // Redirect based on role - all roles go to their dashboard
        switch ($user->role) {
            case 'Administrator':
                return redirect()->route('admin.dashboard');
                
            case 'Managerial':
                return redirect()->route('managerial.dashboard');
                
            case 'Regular':
                return redirect()->route('regular.dashboard');
                
            case 'Contractor':
                return redirect()->route('contractor.dashboard');
                
            default:
                return redirect()->route('home');
        }
    }
}

