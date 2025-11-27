<?php

namespace App\Repositories;

use App\Models\Account;

class AccountsRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new Account();
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function create($data)
    {
        return $this->model->create($data);
    }

    public function update($id, $data)
    {
        $account = $this->find($id);
        $account->update($data);
        return $account;
    }

    public function delete($id)
    {
        $account = $this->find($id);
        return $account->delete();
    }
}
