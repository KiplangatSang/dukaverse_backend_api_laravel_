<?php
namespace App\Http\Middleware;

use App\Helpers\Accounts\Account;
use App\Http\Resources\ApiResource;
use App\Services\AccountService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasSessionAccountMiddleware
{

    public function __construct(protected readonly AccountService $accountService,
        protected readonly ApiResource $apiResourse) {
    }
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

        } else {
            return $this->apiResourse->error('Unauthorized', 403, ['errors' => 'Set the session account first']);

        }
    }
}
