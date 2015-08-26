<?php

namespace Sweepo\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Sweepo\Framework\Command\SweepoAwareCommand;

class TwitterCommand extends SweepoAwareCommand
{
    protected function configure()
    {
        $this->setName('twitter')
            ->setDescription('foo')
            ->addOption('list', null, InputOption::VALUE_REQUIRED, 'Override the default list in your config', []);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $tweets = $container['twitter']->getList($input->getOption('list'));

        if (empty($tweets)) {
            $container['logger']->info('There are no tweets to send');
            $output->writeln('<info>There are no tweets to send</info>');

            return;
        }

        $mail = $container['mail_generator']->generate(['tweets' => $tweets]);
        $container['mailer']->send($mail);

        $output->writeLn('<info>Email sent !</info>');
    }
}
