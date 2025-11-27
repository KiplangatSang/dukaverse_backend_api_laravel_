<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medium extends Model {
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    const ACCOUNT_FILEABLE = "account";
    const USER_FILEABLE    = "user";

    const FILEABLES = [
        self::ACCOUNT_FILEABLE,
        self::USER_FILEABLE,
    ];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function mediumable()
    {
        return $this->morphTo();

    }

    public function user()
    {

        return $this->belongsTo(User::class, 'user_id');
    }

    public function owner()
    {

        return $this->user();
    }

    public static function createMediumFormat($url, $type, $name, $size, $resolution = null)
    {

        $file_object = [
            "url"        => $url,
            "type"       => $type,
            "name"       => $name,
            "size"       => $size,
            "resolution" => $resolution,
        ];

        return $file_object;

    }

    public static function getFileDetails($file)
    {
        //
        $filedetails = [
            "type"       => "",
            "name"       => "",
            "size"       => "",
            "resolution" => "",
        ];
        try {

            $uploaded_file = $file;

            $file_extention = $uploaded_file->getClientOriginalExtension();

            $name = (string) Str::uuid() . "." . $file_extention; // use Illuminate\Support\Str;

            $pathName = $uploaded_file->getPathName();
            // $fileSize = $uploaded_file->getFileSize();

            $filedetails = [
                "type"       => $file_extention,
                "name"       => $pathName,
                "size"       => "",
                "resolution" => "",
            ];

            return $filedetails;
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }
    }

}
