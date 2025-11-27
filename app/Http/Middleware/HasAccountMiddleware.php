<?php
namespace App\Http\Middleware;

use App\Helpers\Accounts\Account;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasAccountMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if ($request->user()) {
            $account      = new Account($request->user());
            $account_list = $account->getAccountList();
            $accounts     = $account_list["accounts"];
            if (! $accounts || count($accounts) < 1) {
                // abort(404, (string) $accounts);

                abort(404, "Register an account first");
            }
            if (count($account_list['accounts']) >= 1) {
                return $next($request);
            } else {
                abort(403, "Set the session account first");
            }
        } else {

            abort(403, "User not found");

        }
    }
}
