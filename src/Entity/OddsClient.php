<?php

namespace App\Entity;

use GuzzleHttp\Client as GuzzleClient;

class OddsClient
{
    /** @var GuzzleClient */
    private $client;

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
    }

    public function get(string $entrypoint, array $options = []): array
    {
        return json_decode($this->client->get($entrypoint, $options)->getBody()->getContents(), true);
    }

}