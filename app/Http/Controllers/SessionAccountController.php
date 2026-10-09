<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;

use App\Http\Requests\StoreSessionAccountRequest;
use App\Http\Requests\UpdateSessionAccountRequest;
use App\Models\SessionAccount;
use App\Services\AccountService;
use Illuminate\Http\Request;

class SessionAccountController extends BaseController
{
    public function __construct(
        protected ApiResource $apiResource,
        private readonly AccountService $accountService
    ) {
        parent::__construct($apiResource);
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $session_accounts = $this->user()->sessionAccount()->get();
        return $this->sendResponse(["session_accounts" => $session_accounts], 'success');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $accounts = $this->getAccountList();

        return $this->sendResponse(["available_accounts" => $accounts], 'success');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $result = $this->accountService->setSessionAccounts($request->all());

        if (! $result) {
            $this->sendError("Error setting up your session account", ["error" => "The session account could not be set up."]);
        }

        return $this->sendResponse(["account" => $this->account()], 'success, Session account set up successfully');

        return $result;
    }

    /**
     * Display the specified resource.
     */
    public function show(SessionAccount $sessionAccount)
    {
        //
        return $sessionAccount;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SessionAccount $sessionAccount)
    {
        //
        return $sessionAccount;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSessionAccountRequest $request, SessionAccount $sessionAccount)
    {
        //
        $validated = $request->validated();
        $result    = $sessionAccount->update(
            $validated,
        );
        return $result;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SessionAccount $sessionAccount)
    {
        //
        $result = SessionAccount::destroy($sessionAccount->id);
        return $result;
    }
}
