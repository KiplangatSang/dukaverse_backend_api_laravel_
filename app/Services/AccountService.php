<?php

namespace App\Services;

use App\Models\Account;
use App\Repositories\AccountsRepository;
use Exception;
use App\Http\Resources\StoreFileResource;
use App\Http\Resources\ResponseHelper;

class AccountService extends BaseService
{

    public function __construct(
       private readonly AccountsRepository $accountRepository,
       protected readonly StoreFileResource $storeFileResource,
       protected readonly   ResponseHelper $responseHelper
    ) {
        parent::__construct($storeFileResource, $responseHelper);
     }

    public function getIndexData()
    {
        $result = $this->accountRepository->getAll();
        $result->toArray();

        return $result;
    }

    public function createAccount($data)
    {
        $result = $this->accountRepository->create($data);
         return $result->toArray();
    }

    public function getShowData($id)
    {
        $account  = $this->accountRepository->find($id);

        if(! $account) {
          return  $this->responseHelper->error('Account not found', 404);

            throw new Exception("Account not found", 404);
        }
        return $account->toArray();
    }

    public function updateAccount($id, $data)
    {
        return $this->accountRepository->update($id, $data);
    }

    public function deleteAccount($id)
    {
        return $this->accountRepository->delete($id);
    }
}
