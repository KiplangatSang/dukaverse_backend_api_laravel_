<?php

namespace App\Helpers;


class NotificationMessage
{

    public $message = "";
    public $url = null;
    public $regulation = null;

    public function __construct($message,$url,$regulation)
    {
    $this->message = $message;
    $this->url = $url;
    $this->regulation = $regulation;
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
}

