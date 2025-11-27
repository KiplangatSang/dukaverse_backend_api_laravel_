<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentDoneMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $transactiondata = null;
    public function __construct($transactiondata)
    {
        //
        $this->transactiondata = $transactiondata;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $transactiondata = $this->transactiondata;
        return $this->from('dukaverse@gmail.com', 'DukaVerse')
            ->subject('Order Placed')
            ->view('emails.paymentdone', compact('transactiondata'));
    }
}
