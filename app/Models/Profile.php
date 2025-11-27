<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model {
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function profileable()
    {
        # code...
        return $this->morphTo();
    }

    public function user()
    {
        # code...
        return $this->belongsTo(User::class, 'user_id');
    }

    public function retail()
    {
        # code...
        return $this->belongsTo(Retail::class, 'retail_id');
    }

    public static function updateProfileWithLocationData($user, $result, $called_ip, $detected_ip, $uses_google_maps = false)
    {

        $profileUpdate = $user->profile()->update(
            [
                "country"          => $result->countryName,
                "country_code"     => $result->countryCode,
                "region"           => $result->regionName,
                "city"             => $result->cityName,
                "ip_address"       => ' called_ip ' . $called_ip . '  detected_ip  ' . $detected_ip,
                "uses_google_maps" => $uses_google_maps,
            ]
        );

        if (! $profileUpdate) {
            return false;
        }

        return true;

    }

}
