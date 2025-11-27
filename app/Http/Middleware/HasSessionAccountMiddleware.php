<?php
namespace App\Http\Middleware;

use App\Helpers\Accounts\Account;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasSessionAccountMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $account = new Account($request->user());

        if ($account->account) {
            info($account->account);
            return $next($request);
        }
        abort(404, $account->error ?? "Set the session account first");
    }
}
