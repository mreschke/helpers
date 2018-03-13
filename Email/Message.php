<?php

namespace Mreschke\Helpers\Email;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Message extends Mailable
{
    use Queueable, SerializesModels;

    // These public variables are the only items visible on the template
    public $subject;
    public $msg;
    public $fromAddress;
    public $fromName;
    public $replyToAddress;

    protected $files;
    protected $template;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $body, $fromAddress, $fromName, $replyToAddress, $files, $template)
    {
        $this->subject = $subject;
        $this->msg = $body;
        $this->fromAddress = $fromAddress;
        $this->fromName = $fromName;
        $this->replyToAddress = $replyToAddress;
        $this->files = $files;
        $this->template = $template;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject($this->subject);
        if ($this->fromName) {
            $this->from($this->fromAddress, $this->fromName);
        } else {
            $this->from($this->fromAddress);
        }
        $this->replyTo($this->replyToAddress);
        if (isset($this->files)) {
            foreach ($this->files as $file) {
                if (file_exists($file)) {
                    $this->attach($file);
                }
            }
        }
        return $this->view($this->template);
    }

}
