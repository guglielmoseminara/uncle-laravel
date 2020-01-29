<?php

namespace UncleProject\UncleLaravel\Helpers;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Markdown;
use Mail;
use Storage;

class MailHelper
{
    // send the mail template to Receiver
    public static function send($receiver, Mailable $email){
        try {
            Mail::to($receiver)->send($email);
        }
        catch (\Exception $e) {

        }

        return  true;
    }

    // render the mail template for browser
    public static function mailView($mailName){
        $markdown = new Markdown(view(), config('mail.markdown'));

        return $markdown->render('mails.'.$mailName);
    }

}