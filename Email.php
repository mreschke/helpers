<?php namespace Mreschke\Helpers;

use Mail;
use File;
use Queue;
use Validator;

/**
 * Email helpers.
 * @copyright 2014 Matthew Reschke
 * @license http://mreschke.com/license/mit
 * @author Matthew Reschke <mail@mreschke.com>
 */
class Email
{

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

				if ($emails) $emailArray = explode(',', $emails);

			} else {
				$emailArray = $emails;
			}

			if ($emailArray) {
				$validEmails = [];
				foreach ($emailArray as $email) {
					if (strlen($email) > 5 && Validator::make(['name' => $email],['name' => 'email'])->passes()) {
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
	 * @return boolean sent success
	 */
	public static function send($to, $subject, $body, $from, $fromName = null, $replyTo = null, $cc = null, $bcc = null, $files = null, $template = 'emails.message')
	{
		// Validate all emails and convert to email arrays
		$to = self::validate($to);
		$from = self::validate($from);
		$replyTo = self::validate($replyTo);
		$cc = self::validate($cc);
		$bcc = self::validate($bcc);
		if (is_null($to)) return false;
		if (is_null($from)) return false;
		if (is_null($fromName)) $fromName = $from;
		if (is_null($replyTo)) $replyTo = $from;
		if (isset($files)) {
			if (is_string($files)) {
				$files = explode(',', $files);
			}
		}

		// Add to queue using closure
		Queue::push(function($job) use($to, $subject, $body, $from, $fromName, $template, $replyTo, $cc, $bcc, $files) {
			
			// Send mail
			Mail::send($template, ['msg' => $body], function($message) use($to, $subject, $body, $from, $fromName, $template, $replyTo, $cc, $bcc, $files) {
				
				// From
				$message->from($from, $fromName);

				// Recipients
				$message->to($to);
				if (isset($cc)) $message->cc($cc);
				if (isset($bcc)) $message->bcc($bcc);

				// Reply To
				$message->replyTo($replyTo);

				// Subject
				$message->subject($subject);

				// Attachments
				if (isset($files)) {
					foreach ($files as $file) {
						if (File::exists($file)) {
							$message->attach($file);
						}
					}
				}

			});

			// Delete queu job after completion (part of a queue closure)
			$job->delete();

		});
	}

}
