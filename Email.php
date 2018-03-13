<?php namespace Mreschke\Helpers;

use Validator;
use Mreschke\Helpers\Email\Message;
use Illuminate\Support\Facades\Mail;

/**
 * Email helpers.
 * @copyright 2014 Matthew Reschke
 * @license http://mreschke.com/license/mit
 * @author Matthew Reschke <mail@mreschke.com>
 */
class Email
{
    /**
     * Send email using laravel templates and queuing system
     * To disable queuing, simply set laravels config/queue.php to sync driver
     * @param  string|array $to array or comma dilimeted string
     * @param  string $subject
     * @param  string $body
     * @param  string $from single email addres
     * @param  string $fromName will use from address if null
     * @param  string $replyTo single email address, will use from address if null
     * @param  string|array $cc array or comma dilimeted string
     * @param  string|array $bcc array or comma dilimeted string
     * @param  string|array $files array or comma dilimeted string
     * @param  string $template email blade template
     * @param  boolean $queue = true
     * @return boolean sent success
     */
    public static function send($to, $subject, $body, $from = null, $fromName = null, $replyTo = null, $cc = null, $bcc = null, $files = null, $template = 'emails.message', $queue = true)
    {
        // Validate all emails, convert to arrays and set defaults
        $to = self::validate($to);
        $from = isset($from) ? $from : config('mail.from.address');
        $fromName = isset($fromName) ? $fromName : config('mail.from.name');
        $from = self::validate($from)[0];
        $replyTo = self::validate($replyTo);
        if ($replyTo) $replyTo = $replyTo[0];
        $cc = self::validate($cc);
        $bcc = self::validate($bcc);
        if (is_null($to)) return false;
        if (is_null($from)) return false;
        if (is_null($fromName)) $fromName = $from;
        if (is_null($replyTo)) $replyTo = $from;
        if (isset($files) && is_string($files)) $files = explode(',', $files);

        // As of Laravel 5.? only mailables can be queued, so Mail::queue() no longer works
        // So we have a generic Email/Message.php file as our mailable
        $mail = Mail::to($to);
        if ($cc) $mail->cc($cc);
        if ($bcc) $mail->bcc($bcc);
        if ($queue) {
            $mail->queue(new Message($subject, $body, $from, $fromName, $replyTo, $files, $template));
        } else {
            $mail->send(new Message($subject, $body, $from, $fromName, $replyTo, $files, $template));
        }
        return true;
    }

    /**
     * Validate one or multiple email addresses and return valid emails in #returnAsArray format
     * @param  string|array $emails single email, dilimeted (,|;) email or array of emails
     * @param  boolean $returnAsArray = true, if false, return as same format they can in (string or array)
     * @return string|array
     */
    public static function validate($emails, $returnAsArray = true)
    {
        if ($emails) {
            $emailArray = null;
            if (is_string($emails)) {
                // Convert string to array
                $emails = trim(str_replace(' ', '', $emails));
                $emails = str_replace('|', ',', $emails);
                $emails = str_replace(';', ',', $emails);
                $emails = str_replace(', ,', ',', $emails);
                $emails = str_replace(',,', ',', $emails);
                if ($emails) {
                    $emailArray = explode(',', $emails);
                }
            } else {
                $emailArray = $emails;
            }

            if ($emailArray) {
                $validEmails = [];
                foreach ($emailArray as $email) {
                    if (strlen($email) > 5 && Validator::make(['name' => $email], ['name' => 'email'])->passes()) {
                        $validEmails[] = $email;
                    }
                }
            }

            if ($validEmails) {
                if ($returnAsArray || is_array($emails)) {
                    return $validEmails;
                } else {
                    return implode(',', $validEmails);
                }
            }
        }
    }
}
