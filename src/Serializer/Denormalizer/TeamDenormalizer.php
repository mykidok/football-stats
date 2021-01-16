<?php

namespace App\Serializer\Denormalizer;

use App\Entity\Team;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TeamDenormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $team = (new Team())
                    ->setName($data['team']['name'])
                    ->setApiId($data['team']['id'])
                    ->setChampionship($data['championship'])
        ;

        return $team;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return Team::class === $type;
    }
}