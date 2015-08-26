<?php

namespace Sweepo\Mailer;

use DateTime;

class MailGenerator
{
    private $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function generate(array $parameters = [])
    {
        $date = new DateTime;

        return new Mail(
            sprintf('Tweets of %s', $date->format('l, F dS, Y')),
            $this->twig->render('mail.html.twig', $parameters)
        );
    }
}
