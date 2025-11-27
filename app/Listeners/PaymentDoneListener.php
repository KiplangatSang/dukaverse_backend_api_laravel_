<?php

namespace App\Listeners;

use App\Events\PaymentDone;
use App\Models\Retail;
use App\Models\User;
use App\Notifications\PaymentMadeNotification;

class PaymentDoneListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public $senderUser = null;
    public $receiverUser = null;

    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\PodcastDone  $event
     * @return void
     */
    public function handle(PaymentDone $event)
    {
        //
        info("Payment has been made " . $event->transaction->id . " time " . now());
        $transaction = $event->transaction;

        if ($transaction->senderAccount) {
            $senderAccountOwner = $transaction->senderAccount->accountable;
            if ($senderAccountOwner instanceof Retail) {
                $this->senderUser = $senderAccountOwner->retailable;
                info("sender retail");
            } elseif ($senderAccountOwner instanceof User) {
                $this->senderUser = $senderAccountOwner;
                info("sender user");
            }
        }

        if ($transaction->receiverAccount) {
            $receiverAccountOwner = $transaction->receiverAccount->accountable;
            if ($receiverAccountOwner instanceof Retail) {
                $this->receiverUser = $receiverAccountOwner->retailable;
                info("receiver retail");
            } elseif ($receiverAccountOwner instanceof User) {
                $this->receiverUser = $receiverAccountOwner;
                info("receiver user");
            }
        }

        $transaction['sender'] = $this->senderUser;
        $transaction['receiver'] = $this->receiverUser;

        $senderMessage = "Success, Congracts your payment has been successful";
        $receiverMessage = "Success , You have received a payment to your account";
        $this->senderUser->notify(new PaymentMadeNotification($transaction, $senderMessage));
        $this->receiverUser->notify(new PaymentMadeNotification($transaction, $receiverMessage));
    }
}
