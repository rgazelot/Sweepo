<?php

use Pimple\Container;

use Symfony\Component\Yaml\Yaml;

use Sweepo\Service\Twitter;
use Sweepo\Mailer\Mailer;
use Sweepo\Mailer\MailGenerator;

class Sweepo
{
    private $parameters;
    private $config;
    private $container;

    public function __construct()
    {
        $this->parameters = Yaml::parse(file_get_contents(__DIR__.'/config/parameters.yml'))['parameters'];
        $this->parameters['kernel.root_dir'] = __DIR__;

        // Create the cache folder
        if (false === @mkdir($this->parameters['kernel.root_dir'].'/cache', 0777, true) && !is_dir($this->parameters['kernel.root_dir'].'/cache')) {
            throw new \RuntimeException("Unable to create the cache directory\n");
        }

        $this->config = Yaml::parse(file_get_contents(__DIR__.'/config/config.yml'));
        $this->config['twitter']['storage_path'] = $this->parameters['kernel.root_dir'].'/'.$this->config['twitter']['storage_path'];

        // Create the twitter cache storage
        if (false === @mkdir($this->config['twitter']['storage_path'], 0777, true) && !is_dir($this->config['twitter']['storage_path'])) {
            throw new \RuntimeException("Unable to create the twitter cache directory\n");
        }

        $this->container = new Container;
        $this->container['parameters'] = $this->parameters;
        $this->container['config'] = $this->config;

        $this->container['twitter'] = function() {
            if (!isset($this->parameters['twitter.owner_screen_name'])) {
                throw new InvalidArgumentException('Missing twitter.ownerScreenName in your configuration file');
            }

            return new Twitter(
                $this->parameters['twitter.consumer_key'],
                $this->parameters['twitter.consumer_secret'],
                $this->parameters['twitter.oauth_token'],
                $this->parameters['twitter.oauth_token_secret'],
                $this->parameters['twitter.owner_screen_name'],
                $this->config['twitter']['storage_path'],
                isset($this->parameters['twitter.limit']) ? $this->parameters['twitter.limit'] : 1000,
                isset($this->parameters['twitter.list']) ? $this->parameters['twitter.list'] : null
            );
        };

        $this->container['mailer'] = function() {
            return new Mailer(
                $this->parameters['mailer.host'],
                $this->parameters['mailer.port'],
                $this->parameters['mailer.user'],
                $this->parameters['mailer.password'],
                $this->parameters['mailer.sender'],
                $this->parameters['twitter.recipients'],
                $this->parameters['mailer.sender_name']
            );
        };

        $this->container['twig'] = function() {
            $loader = new Twig_Loader_Filesystem(__DIR__.'/../src/Mailer/Resources/views');
            return new Twig_Environment($loader, array(
                'cache' => false,
            ));
        };

        $this->container['mail_generator'] = function() {
            return new MailGenerator($this->container['twig']);
        };
    }

    public function getContainer()
    {
        return $this->container;
    }
}
