<?php
namespace App\Http\Middleware;

use App\Http\Resources\ApiResource;
use App\Services\AccountService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HasAccountMiddleware
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

        if(!Auth::check()){
            return $this->apiResourse->error('Unauthorized', 401, ['errors' => 'Please login first']);
        }
        if ($request->user()) {
            $account_list = $this->accountService->getAccounts('retailer');
            if (isset($account_list["error"])) {
                return $this->apiResourse->error($account_list["message"], 500, ['errors' => $account_list["error"]]);
            }
            $accounts = $account_list["data"]["accounts"];

            if (! $accounts || count($accounts) < 1) {
                return $this->apiResourse->error('No account found', 404, ['errors' => 'Register an account first']);
            }
            if (count($accounts) >= 1) {
                return $next($request);
            } else {
                return $this->apiResourse->error('No account found', 403, ['errors' => 'Register an account first']);
            }
        } else {

            return $this->apiResourse->error('Server Error', 500, ['errors' => 'Account Server Error']);

        }
    }
}
