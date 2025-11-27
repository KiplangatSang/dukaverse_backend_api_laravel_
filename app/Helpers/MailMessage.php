<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class MailMessage
{

    public $message = "";
    public $url = null;
    public $regulation = null;
    public $can_reply = true;

    public function __construct($message,$url,$regulation,$can_reply)
    {
    $this->message = $message;
    $this->url = $url;
    $this->regulation = $regulation;
    $this->can_reply = $can_reply;
    }

    function setMessage($message){
     $this->message = $message;
    }

    function setUrl($url){
        $this->url = $url;
    }

    function setRegulation($regulation){
        $this->regulation = $regulation;
    }

    function setCanReply($can_reply){
        $this->can_reply = $can_reply;
    }
}

