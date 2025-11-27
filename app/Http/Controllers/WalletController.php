<?php
namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use App\Models\Wallet;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletController extends BaseController
{
    public function __construct(
        private readonly AuthService $authService,
        ApiResource $apiResource
    ) {
        parent::__construct($apiResource);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $wallets = $this->account()->wallets()->with('transactions')->get();

        if (! $wallets) {
            return $this->sendError('Data could not be fetched', ["wallets" => $wallets]);
        }
        return $this->sendResponse(["wallets" => $wallets], "Data fetched successfully");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //

        $wallet_status = Wallet::WALLET_STATUS;

        if (! $wallet_status) {
            return $this->sendError('Data could not be fetched', ["wallet_status" => $wallet_status]);
        }
        return $this->sendResponse(["wallet_status" => $wallet_status], "Data fetched successfully");

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $validator = Validator::make($request->all(), []);

        if ($validator->fails()) {
            return $this->sendError('Bad request', $validator->errors());
        }
        $wallet = $this->account()->wallets()->create([
            "name"            => $request->name,
            "balance"         => 0,
            "pending_balance" => 0,
            "frozen_balance"  => 0,
            "status"          => Wallet::WALLET_STATUS_ACTIVE,
            "user_id"         => $request->user_id ?? auth()->id(),
        ]);

        if (! $wallet) {
            return $this->sendError('Data could not be created', ["wallet" => $wallet]);
        }

        return $this->sendResponse(["wallet" => $wallet], "Wallet created successfully");
    }

    /**
     * Display the specified resource.
     */
    public function show($wallet)
    {
        //
        $wallet = Wallet::where('id', $wallet)->first();

        if (! $wallet) {
            return $this->sendError('Data could not be fetched', ["wallet" => $wallet]);
        }

        return $this->sendResponse(["wallet" => $wallet], "Wallet fetched successfully");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Wallet $wallet)
    {
        //
        //
        $wallet = Wallet::where('id', $wallet)->first();

        if (! $wallet) {
            return $this->sendError('Data could not be fetched', ["wallet" => $wallet]);
        }

        $wallet_status = Wallet::WALLET_STATUS;

        if (! $wallet_status) {
            return $this->sendError('Data could not be fetched', ["wallet_status" => $wallet_status]);
        }
        return $this->sendResponse(["wallet_status" => $wallet_status, "wallet" => $wallet], "Data fetched successfully");

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Wallet $wallet)
    {
        //

        $wallet = Wallet::where('id', $wallet)->first();

        if (! $wallet) {
            return $this->sendError('Data could not be fetched', ["wallet" => $wallet]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request', $validator->errors());
        }
        $wallet = $this->account()->wallets()->create([
            "name"            => $request->name ?? $wallet->name,
            "balance"         => $request->balance ?? $wallet->balance,
            "pending_balance" => $request->pending_balance ?? $wallet->pending_balance,
            "frozen_balance"  => $request->frozen_balance ?? $wallet->frozen_balance,
            "status"          => $request->status ?? $wallet->status,
            "user_id"         => $request->user_id ?? auth()->id(),
        ]);

        if (! $wallet) {
            return $this->sendError('Data could not be updated', ["wallet" => $wallet]);
        }

        $wallet = Wallet::where('id', $wallet)->first();

        return $this->sendResponse(["wallet" => $wallet], "Wallet updated successfully");

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($wallet)
    {
        //

        $wallet = Wallet::destroy($wallet);

        if (! $wallet) {
            return $this->sendError('Data could not be updated', ["wallet" => $wallet]);
        }

        return $this->sendResponse(["wallet" => $wallet], "Wallet updated successfully");

    }
}
