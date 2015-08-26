<?php

namespace Sweepo\Service;

use InvalidArgumentException;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use Abraham\TwitterOAuth\TwitterOAuth;

class Twitter
{
    private $twitter;
    private $ownerScreenName;
    private $storagePath;
    private $limit;
    private $defaultList;
    private $logger;

    public function __construct($consumerKey, $consumerSecret, $oauthToken, $oauthTokenSecret, $ownerScreenName, $storagePath, $limit = 50, $defaultList = null, LoggerInterface $logger = null)
    {
        $this->twitter = new TwitterOAuth($consumerKey, $consumerSecret, $oauthToken, $oauthTokenSecret);
        $this->ownerScreenName = $ownerScreenName;
        $this->storagePath = $storagePath;
        $this->limit = $limit;
        $this->defaultList = $defaultList;
        $this->logger = $logger ?: new NullLogger;
    }

    public function getList($list = null)
    {
        $parameters = [
            'slug' => !empty($list) ? $list : $this->defaultList,
            'owner_screen_name' => $this->ownerScreenName,
        ];

        // If a previous since_id was stored, use it.
        if (file_exists($this->storagePath.'/since.txt')) {
            $parameters['since_id'] = (int) file_get_contents($this->storagePath.'/since.txt');
        }

        $this->logRequest($parameters);
        $results = $this->twitter->get("lists/statuses", $parameters);
        $this->checkErrors($results);

        // Pagniate if needed
        while (!empty($results) && isset($parameters['since_id']) && end($results)->id > $parameters['since_id']) {
            $parameters['max_id'] = end($results)->id - 1;

            $this->logRequest($parameters);
            $newResults = $this->twitter->get("lists/statuses", $parameters);
            $this->checkErrors($newResults);
            $this->logFetch(count($newResults));

            if (empty($newResults)) {
                break;
            }

            $results = array_merge($results, $newResults);
        }

        // If there are results, store the last id to paginate.
        if (!empty($results)) {
            file_put_contents($this->storagePath.'/since.txt', $results[0]->id);
        }

        $this->logFetch(count($results));

        return $results;
    }

    private function logRequest(array $parameters)
    {
        $this->logger and $this->logger->info(sprintf("Fetch the tweets of the list %s", $parameters['slug']), [
            'slug' => $parameters['slug'],
            'owner_screen_name' => $parameters['owner_screen_name'],
            'since_id' => isset($parameters['since_id']) ? $parameters['since_id'] : null,
            'max_id' => isset($parameters['max_id']) ? $parameters['max_id'] : null,
        ]);
    }

    private function logFetch($count)
    {
        $this->logger and $this->logger->info(sprintf("%d tweets fetched", $count));
    }

    private function checkErrors($results)
    {
        if (!is_array($results) && $results->errors) {
            $e = new InvalidArgumentException(sprintf('Twitter error - %s', $message = !isset($results->errors[0]->message) ? '' : $results->errors[0]->message));
            $this->logger and $this->logger->critical($e->getMessage());

            throw $e;
        }
    }
}
