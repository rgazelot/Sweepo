<?php

namespace Sweepo\Framework\Command;

use Symfony\Component\Console\Command\Command;

class SweepoAwareCommand extends Command
{
    public function getContainer()
    {
        return $this->getApplication()->getSweepo()->getContainer();
    }
}
