<?php


namespace app\services;


class Mail
{
    public static function send($to, $to_user_name, $user_email, $subject, $text, $html)
    {

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("admin@blog.tolan.me", "Blog BT");
        $email->setSubject($subject);
        $email->addTo($to, $to_user_name);
        $email->setReplyTo($user_email);
        $email->addContent("text/plain", $text);
        $email->addContent(
            "text/html", $html
        );
        $sendgrid = new \SendGrid(SENDGRID_API_KEY);
        try {
            $response = $sendgrid->send($email);
            Session::addMessage('Mail envoyer avec succes');
            return Redirect::to('contact');
        } catch (Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }
    }

}
