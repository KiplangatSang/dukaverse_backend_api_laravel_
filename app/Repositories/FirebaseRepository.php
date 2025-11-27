<?php
namespace App\Repositories;

use App\Helpers\Accounts\Account;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FirebaseRepository
{

    protected $factory = null;
    protected $account = null;
    public function __construct($account = null)
    {
        // $this->factory = (new Factory)
        //     // ->withServiceAccount("C:\\xampp\\htdocs\\DukaVerse\\storage\\app\\firebase\\firebase_credentials.json")
        //     ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')))
        //     ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));
        // // dd(env('FIREBASE_DATABASE_URL'));
        $this->account = $account;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *
     * allow write: if "auth.token.email === 'kiplangatsang425@gmail.com'";
     *
     */

    public function store($user, $folder, $file)
    {
        //
        try {
            $storage       = app('firebase.storage'); // This is an instance of Google\Cloud\Storage\StorageClient from kreait/firebase-php library
            $defaultBucket = $storage->getBucket();
            $image         = $file;
            $name          = (string) Str::uuid() . "." . $image->getClientOriginalExtension(); // use Illuminate\Support\Str;

            $pathName = $image->getPathName();

            $package  = $this->firebaseRepositories($this->account, $user);
            $filename = $package . $folder . "/" . $name;
            // $file = fopen($pathName, 'r');
            $file   = fopen($pathName, 'r');
            $object = $defaultBucket->upload($file, [
                'name'          => $filename,
                'predefinedAcl' => 'publicRead',
            ]);
            $image_url = 'https://storage.googleapis.com/' . env('FIREBASE_PROJECT_ID') . '.appspot.com/' . $filename;
            //dd($file);

            //https://storage.googleapis.com/dukaverse-e4f47.appspot.com/1/bf8af7e2-275d-4327-93a5-144fe2f42e24
            return $image_url;
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }
    }

    public function firebaseRepositories($account = null, User $user)
    {
        # code...
        $package = "";
        if ($account) {
            $package = "client/accounts/" . $account->name . $account->id . "/";
        } else {
            $package = "client/user/" . $user->id . "/";
        }
        return $package;
    }
}
