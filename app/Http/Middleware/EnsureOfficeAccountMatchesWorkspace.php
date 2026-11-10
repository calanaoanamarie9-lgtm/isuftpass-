<?php

namespace App\Http\Middleware;

use App\Enums\Office;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps a generic office account on its own office's page.
 *
 * The seven offices with a page of their own used to be reachable only by
 * their dedicated roles (ssc, accounting, library), so an office account was
 * always safe in the generic workspace. Once those accounts are sent to their
 * own page, the same routes would otherwise let a Property Management account
 * open /library and read Library's schedule - the controllers build their page
 * from the office in the URL, not from the caller. So an office account may
 * only enter the group its own office maps to; every other role is left to the
 * role middleware as before.
 */
class EnsureOfficeAccountMatchesWorkspace
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $workspace): Response
    {
        $user = $request->user();

        if ($user
            && $user->role === User::ROLE_OFFICE
            && Office::workspaceKey($user->officeScope()) !== $workspace) {
            abort(403);
        }

        return $next($request);
    }
}
