<?php

namespace Sweepo\Mailer;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use Swift_Message;
use Swift_Mailer;
use Swift_SmtpTransport;

class Mailer
{
    private $host;
    private $port;
    private $user;
    private $password;
    private $sender;
    private $recipients;
    private $senderName;
    private $logger;

    public function __construct($host, $port, $user, $password, $sender, $recipients, $senderName = null, LoggerInterface $logger = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->sender = $sender;
        $this->recipients = $recipients;
        $this->senderName = $senderName;
        $this->logger = $logger ?: new NullLogger;
    }

    public function send(Mail $mail)
    {
        $transport = Swift_SmtpTransport::newInstance($this->host, $this->port);
        $transport->setUsername($this->user);
        $transport->setPassword($this->password);

        $mailer = Swift_Mailer::newInstance($transport);

        $message = Swift_Message::newInstance();
        $message->setSubject($mail->getSubject());
        $message->setFrom([$this->sender => $this->senderName]);
        $message->setTo($this->recipients);
        $message->setBody($mail->getHtml(), 'text/html');

        try {
            $mailer->send($message);
        } catch (\Exception $e) {
            $this->logger and $this->logger->critical($e->getMessage());
        }

        $this->logger and $this->logger->info('Email sent !');
    }
}
