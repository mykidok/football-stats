<?php

namespace App\Entity;

use GuzzleHttp\Client as GuzzleClient;

class Client
{
    const REQUEST_GET = 'get';

    /** @var GuzzleClient $client */
    private $client;

    public function __construct(GuzzleClient $client, $apiKey)
    {
        $this->client = $client;
    }


    public function get($entrypoint, array $options = [])
    {
        return json_decode($this->client->get($entrypoint, $options)->getBody()->getContents());
    }
}