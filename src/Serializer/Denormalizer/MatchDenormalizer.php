<?php

namespace App\Serializer\Denormalizer;

use App\Entity\Match;
use App\Entity\Team;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MatchDenormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $match = new Match();

        if (isset($data['homeTeam'])) {
            $homeTeam = (new Team())
                            ->setId($data['homeTeam']['id'])
                            ->setName($data['homeTeam']['name']);

            $match->setHomeTeam($homeTeam);
        }

        if (isset($data['awayTeam'])) {
            $awayTeam = (new Team())
                            ->setId($data['awayTeam']['id'])
                            ->setName($data['awayTeam']['name']);

            $match->setAwayTeam($awayTeam);
        }

        return $match;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return Match::class === $type;
    }
}