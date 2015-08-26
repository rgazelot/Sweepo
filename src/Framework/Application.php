<?php

namespace Sweepo\Framework;

use \Sweepo;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    private $sweepo;

    public function __construct(Sweepo $sweepo)
    {
        $this->sweepo = $sweepo;

        parent::__construct();
    }

    public function getSweepo()
    {
        return $this->sweepo;
    }
}
