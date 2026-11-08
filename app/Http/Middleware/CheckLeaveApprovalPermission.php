<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Leave;

class CheckLeaveApprovalPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Employees cannot approve leave
        if ($user->isEmployee()) {
            abort(403, 'Unauthorized action. Employees cannot approve leave requests.');
        }

        // For specific leave approval actions, check if the user can approve this specific leave
        if ($request->route('id')) {
            $leave = Leave::findOrFail($request->route('id'));

            if (!$user->canApproveLeave($leave)) {
                abort(403, 'Unauthorized action. You can only approve leave requests of your subordinates.');
            }
        }

        return $next($request);
    }
}
