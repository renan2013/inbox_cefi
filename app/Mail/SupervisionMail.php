<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupervisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $messageBody;
    public $courseTitle;

    /**
     * Create a new message instance.
     */
    public function __construct($subject, $messageBody, $courseTitle = '')
    {
        $this->subject = $subject;
        $this->messageBody = $messageBody;
        $this->courseTitle = $courseTitle;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->html(nl2br($this->messageBody));
    }
}
