<?php
namespace App\Helpers\Locations;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stevebauman\Location\Facades\Location;

class UserLocationWithIPAddress
{

    public function getUserLocation(Request $request)
    {

        $user = User::where('id', Auth::id())->first();

        $result = $this->getLocation();

        if ($result) {
            Profile::updateProfileWithLocationData($user, $result, $called_ip = $result->ip, $this->getIPAddress());

        }
        return ["result" => $result, 'ip' => $this->getIPAddress()];
    }

    public function getLocation()
    {
        # code...
        $currentUserInfo = null;
        if (env('APP_URL') == "http://localhost") {
            $currentUserInfo = Location::get($this->getIp());
        } else {
            $currentUserInfo = Location::get($this->getIPAddress());
        }

        return $currentUserInfo;
    }

    public function getIPAddress()
    {
        // List of possible headers where the IP address could be stored
        $headers = [
            'HTTP_CLIENT_IP',           // IP from shared internet connections
            'HTTP_X_FORWARDED_FOR',     // IP forwarded by proxies
            'HTTP_X_FORWARDED',         // IP forwarded by some proxies
            'HTTP_X_CLUSTER_CLIENT_IP', // IP forwarded by load balancers
            'HTTP_FORWARDED_FOR',       // IP from forwarded requests
            'HTTP_FORWARDED',           // IP from forwarded requests (other format)
            'REMOTE_ADDR',              // Default IP address
        ];

        foreach ($headers as $header) {
            if (array_key_exists($header, $_SERVER)) {
                return $_SERVER[$header];
                // Split the header value in case multiple IPs are present
                $ips = explode(',', $_SERVER[$header]);

                // Iterate through all IPs
                foreach ($ips as $ip) {
                    $ip = trim($ip); // Remove any extra spaces

                    // Validate the IP address and check if it's not a private or reserved IP range
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }

        // If no valid IP was found, return "unknown"
        return "unknown";

    }

    public function getIp()
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'] as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $_SERVER;
                    } else {
                        return $ip;
                    }
                }
            } else {
                return "unknown";
            }
        }
    }

}
