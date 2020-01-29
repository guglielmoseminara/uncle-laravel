<?php

namespace UncleProject\UncleLaravel\Helpers;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Markdown;
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

    public static function mailView($mailName){
        $markdown = new Markdown(view(), config('mail.markdown'));

        return $markdown->render('mails.'.$mailName);
    }

}