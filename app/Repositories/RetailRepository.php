<?php
namespace App\Repositories;

use App\Helpers\notifications\DukaverseNotification;
use App\Models\Retail;
use App\Models\User;
use Carbon\Carbon;

class RetailRepository
{
    private $user;
    private $account;
    public function __construct(User $user, $account)
    {
        $this->user    = $user;
        $this->account = $account;

    }

    public function retails()
    {
        $retails = $this->user->retails()->first();
        return $retails;
    }

    public function storeRetailInSession($retailId)
    {

        # code...
        $user   = User::where('id', Auth::id())->first();
        $retail = Retail::where('id', $retailId)->first();
        //dd( $retail);
        $result = $user->sessionRetail()->updateOrCreate(
            [
                'retailable_id' => Auth::id(),
            ],
            [
                'retail_id'    => $retail->id,
                'retailNameId' => $retail->retail_Id,
            ]
        );
        if (! $result) {
            return false;
        }

        return $retail;
    }

    public function getPaymentPreferences()
    {
        # code...

        $paymentPref = [
            "mpesapaybill" => "mpesapaybill",
            "mpesatill"    => "mpesatill",
            "dukaverse"    => "dukaverse",
        ];

        return $paymentPref;
    }

    public static function checkDueNotifications($account)
    {

        $timezone = env('timezone');

        $notification_required = false;

        $account = $account::where('is_active', true)
            ->with('settings')
            ->get();

        $settings = $account
            ->pluck('settings')
            ->flatten();

        if ($settings->timezone) {
            $timezone = $settings->timezone;
        }

        foreach ($settings as $setting) {
            $notification_schedules = $setting->notificationTime;

            $now                   = Carbon::now();
            $notification_required = new DukaverseNotification();

            if (count($notification_schedules) > 0) {
                foreach ($notification_schedules as $notification_schedule) {

                    if ($notification_schedule &&
                        $notification_schedule->time->between(Carbon::today($timezone)->setTime(21, 0), Carbon::today($timezone)->setTime(22, 30))
                    ) {

                        $notification_required = new DukaverseNotification($notification_schedule->time,
                            $notification_schedule->time,
                            "New Message");

                    }
                }
            }
        }

        return $notification_required;
    }
}
