<?php

namespace App\Serializer\Denormalizer;

use App\Entity\Game;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class GameDenormalizer implements DenormalizerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $teamRepository = $this->em->getRepository(Team::class);
        /** @var Team $homeTeam */
        $homeTeam = $teamRepository->findOneBy(['apiId' => $data['homeTeam']['id']]);

        /** @var Team $awayTeam */
        $awayTeam = $teamRepository->findOneBy(['apiId' => $data['awayTeam']['id']]);

        $previsionalNbGoals = ($homeTeam->getNbGoalsPerMatchHome() + $awayTeam->getNbGoalsPerMatchAway()) / 2;

        $game = (new Game())
                        ->setApiId($data['id'])
                        ->setHomeTeam($homeTeam)
                        ->setAwayTeam($awayTeam)
                        ->setDate((new \DateTime($data['utcDate']))->modify('+ 1 hour'))
                        ->setChampionship($data['championship'])
                        ->setPrevisionalNbGoals(round($previsionalNbGoals, 3))
        ;

        return $game;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return Game::class === $type;
    }
}