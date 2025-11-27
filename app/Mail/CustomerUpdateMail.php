<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $customersdata = null;
    public function __construct($customersdata)
    {
        //
        $this->customersdata =$customersdata;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $customersdata=  $this->customersdata;
        return $this->from('dukaverse@gmail.com', 'DukaVerse')
        ->subject('This customer account has been updated')
        ->view('customerupdate',compact('customersdata'));
    }
}
