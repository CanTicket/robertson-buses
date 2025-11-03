<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Check if user has the required role
        if ($user->role !== $role) {
            // Instead of 403, redirect to user's appropriate dashboard based on their role
            return $this->redirectToUserDashboard($user, $role);
        }

        return $next($request);
    }
    
    /**
     * Redirect user to their appropriate dashboard based on their role.
     *
     * @param  \App\Models\User  $user
     * @param  string  $requiredRole
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToUserDashboard($user, string $requiredRole)
    {
        // Redirect based on user's actual role
        switch ($user->role) {
            case 'Administrator':
                return redirect()->route('admin.dashboard')
                    ->with('error', "You need {$requiredRole} role to access that page. Redirected to Admin Dashboard.");
                    
            case 'Managerial':
                return redirect()->route('managerial.dashboard')
                    ->with('error', "You need {$requiredRole} role to access that page. Redirected to Manager Dashboard.");
                    
            case 'Regular':
                return redirect()->route('regular.dashboard')
                    ->with('error', "You need {$requiredRole} role to access that page. Redirected to Driver Dashboard.");
                    
            case 'Contractor':
                return redirect()->route('contractor.dashboard')
                    ->with('error', "You need {$requiredRole} role to access that page. Redirected to Contractor Dashboard.");
                    
            default:
                return redirect()->route('home')
                    ->with('error', "Unauthorized. This action requires {$requiredRole} role.");
        }
    }
}
