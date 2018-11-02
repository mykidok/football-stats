<?php

namespace App\Serializer\Denormalizer;


use App\Entity\Standing;
use App\Entity\Table;
use App\Entity\Team;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class StandingDenormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $standing = new Standing();

        $table = new Table();
        foreach ($data['table'] as $datum) {
            $nbGoalsPerMatch = ($datum['goalsFor'] + $datum['goalsAgainst']) / $datum['playedGames'];
            $table->addTeam((new Team())
                            ->setId($datum['team']['id'])
                            ->setName($datum['team']['name'])
                            ->setNbGoalsPerMatch($nbGoalsPerMatch)
                )
            ;
        }

        $standing->setTable($table);

        return $standing;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return Standing::class === $type;
    }
}