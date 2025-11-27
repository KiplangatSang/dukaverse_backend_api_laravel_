<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $verificationData = array();

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($verificationData)
    {
        //
        $this->verificationData = $verificationData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $verificationData = $this->verificationData;
        return $this->view('emails.verificationemail',compact('verificationData'));
    }
}
