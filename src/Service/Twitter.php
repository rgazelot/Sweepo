<?php

namespace Sweepo\Service;

use InvalidArgumentException;

use Abraham\TwitterOAuth\TwitterOAuth;

class Twitter
{
    private $twitter;
    private $ownerScreenName;
    private $storagePath;
    private $limit;
    private $defaultList;

    public function __construct($consumerKey, $consumerSecret, $oauthToken, $oauthTokenSecret, $ownerScreenName, $storagePath, $limit = 50, $defaultList = null)
    {
        $this->twitter = new TwitterOAuth($consumerKey, $consumerSecret, $oauthToken, $oauthTokenSecret);
        $this->ownerScreenName = $ownerScreenName;
        $this->storagePath = $storagePath;
        $this->limit = $limit;
        $this->defaultList = $defaultList;
    }

    public function getList($list = null)
    {
        $parameters = [
            'slug' => !empty($list) ? $list : $this->defaultList,
            'owner_screen_name' => $this->ownerScreenName,
        ];

        // If a previous since_id was stored, use it.
        if (file_exists($this->storagePath.'/since.txt')) {
            $parameters['since_id'] = file_get_contents($this->storagePath.'/since.txt');
        }

        $result = $this->twitter->get("lists/statuses", $parameters);

        if (!is_array($result) && $result->errors) {
            throw new InvalidArgumentException(sprintf('Twitter error - %s', $message = !isset($result->errors[0]->message) ? '' : $result->errors[0]->message));
        }

        // If there are results, store the last id to paginate.
        if (!empty($result)) {
            file_put_contents($this->storagePath.'/since.txt', $result[0]->id);
        }

        return $result;
    }
}
