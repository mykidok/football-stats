<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class Client
{
    /** @var GuzzleClient */
    private $client;

    /** @var DenormalizerInterface */
    private $denormalizer;

    public function __construct(GuzzleClient $client, DenormalizerInterface $denormalizer, $apiKey)
    {
        $this->client = $client;
        $this->denormalizer = $denormalizer;
    }

    public function getMatchDay(array $options = []): MatchDay
    {
        $data = json_decode($this->client->get('matches', $options)->getBody()->getContents(), true);

        $matchDay = new MatchDay();
        foreach ($data['matches'] as $item) {
            /** @var Match $match */
            $match = $this->denormalizer->denormalize($item, Match::class, JsonEncoder::FORMAT);
            $matchDay->addMatch($match);
        }

        return $matchDay;
    }

    public function getGlobalStanding(string $entrypoint, array $options = []): GlobalStanding
    {
        $data = json_decode($this->client->get($entrypoint, $options)->getBody()->getContents(), true);

        $globalStanding = new GlobalStanding();
        foreach ($data['standings'] as $item) {
            if ('TOTAL' === $item['type']) {
                continue;
            }

            /** @var Standing $standing */
            $standing = $this->denormalizer->denormalize($item, Standing::class, JsonEncoder::FORMAT);

            switch ($item['type']) {
                case 'HOME':
                    $globalStanding->setHomeStanding($standing);
                    break;
                case 'AWAY':
                    $globalStanding->setAwayStanding($standing);
            }
        }

        return $globalStanding;
    }
}