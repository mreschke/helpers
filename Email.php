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
     * @param  boolean $queued = true
     * @return boolean sent success
     */
    public static function send($to, $subject = null, $body = null, $from = null, $fromName = null, $replyTo = null, $cc = null, $bcc = null, $files = null, $template = 'emails.message', $queued = true)
    {
        // Default Email Options
        $options = [
            'to' => '',
            'subject' => 'Email',
            'body' => '',
            'from' => config('mail.from.address'),
            'fromName' => config('mail.from.name'),
            'replyTo' => config('mail.from.address'),
            'cc' => null,
            'bcc' => null,
            'files' => null,
            'template' => 'emails.message',
            'queued' => true,
        ];

        // If $to is an associative array, use array based parameters
        if (is_array($to) && array_keys($to) !== range(0, count($to) - 1)) {
            // Merge array based options with default options
            $options = array_merge($options, $to);
        } else {
            // Merge PHP function parameter based options with default options
            foreach ($options as $key => $value) {
                if (isset(${$key})) $options[$key] = ${$key};
            }
        }

        // Convet all strings to arrays and validate all email addresses
        $options['to'] = self::validate($options['to']);
        $options['cc'] = self::validate($options['cc']);
        $options['bcc'] = self::validate($options['bcc']);
        $options['from'] = self::validate($options['from'], false);
        $options['replyTo'] = self::validate($options['replyTo'], false);

        // Files comma string to array
        if (isset($options['files']) && is_string($options['files'])) {
            $options['files'] = explode(',', $options['files']);
        }

        // As of Laravel 5.? only mailables can be queued, so Mail::queue() no longer works
        // So we have a generic Email/Message.php file as our mailable
        $mail = Mail::to($options['to']);
        if ($options['cc']) $mail->cc($options['cc']);
        if ($options['bcc']) $mail->bcc($options['bcc']);
        if ($options['queued']) {
            // Queued (asynchronous)
            $mail->queue(new Message($options['subject'], $options['body'], $options['from'], $options['fromName'], $options['replyTo'], $options['files'], $options['template']));
        } else {
            // NOT Queued (synchronous)
            $mail->send(new Message($options['subject'], $options['body'], $options['from'], $options['fromName'], $options['replyTo'], $options['files'], $options['template']));
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
