#!/usr/bin/env php
<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/Sweepo.php';

use Sweepo\Command\TwitterCommand;
use Sweepo\Framework\Application;

$console = new Application(new Sweepo);
$console->add(new TwitterCommand());
$console->run();
