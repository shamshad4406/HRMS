<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EmploymentDetail;

class CheckEmployeeStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Check if user is authenticated and is not admin
        if ($user && $user->role !== 'admin' && $user->personal_information_id) {
            $employmentDetail = EmploymentDetail::where('personal_information_id', $user->personal_information_id)->first();

            // If employee is inactive, logout and redirect
            if ($employmentDetail && $employmentDetail->status === 'inactive') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Your account has been deactivated. Please contact your administrator.');
            }
        }

        return $next($request);
    }
}
