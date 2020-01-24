<?php

namespace UncleProject\UncleLaravel\Helpers;

use Illuminate\Mail\Mailable;
use Mail;
use Storage;

class MailHelper
{
    public static function send($receiver, Mailable $email){
        try {
            Mail::to($receiver)->send($email);
        }
        catch (\Exception $e) {

        }

        return  true;
    }

}