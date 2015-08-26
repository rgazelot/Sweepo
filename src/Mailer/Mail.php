<?php

namespace Sweepo\Mailer;

class Mail
{
    private $subject;
    private $html;

    public function __construct($subject, $html)
    {
        $this->subject = $subject;
        $this->html = $html;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getHtml()
    {
        return $this->html;
    }
}
