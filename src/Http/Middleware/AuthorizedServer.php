<?php
namespace Deegitalbe\TrustupProAdminCommon\Http\Middleware;

use Closure;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;

/**
 * Restricting request to authorized servers only.
 */
class AuthorizedServer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $authorization_key = $request->header('X-SERVER-AUTHORIZATION');
        if (!$authorization_key
            || !Package::authorization()
            || $authorization_key !==  Package::authorization()
        ):
            return response(['message' => "Unauthenticated."], 401);
        endif;

        return $next($request);
    }
}