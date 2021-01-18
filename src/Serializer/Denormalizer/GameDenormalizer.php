<?php

namespace App\Serializer\Denormalizer;

use App\Entity\Game;
use App\Entity\Team;
use App\Entity\TeamHistoric;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class GameDenormalizer implements DenormalizerInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array()): ?Game
    {
        $teamRepository = $this->em->getRepository(Team::class);
        /** @var Team $homeTeam */
        $homeTeam = $teamRepository->findOneBy(['apiId' => $data['teams']['home']['id']]);

        /** @var Team $awayTeam */
        $awayTeam = $teamRepository->findOneBy(['apiId' => $data['teams']['away']['id']]);

        $scoreTable = [];
        $maxResult = 0;
        $lessThanPercentage = 0;
        $moreThanPercentage = 0;
        $previsionalWinner = null;

        $nbGoalsExpectedMost = null;
        $nbGoalsIsSameAsExpected = null;

        if ((null !== $homeTeam->getNbGoalsPerMatchHome() && null !== $awayTeam->getNbGoalsPerMatchAway()) && ($homeTeam->getNbGoalsPerMatchHome() + $awayTeam->getNbGoalsPerMatchAway() > 0)) {
            $previsionalNbGoals = ($homeTeam->getNbGoalsPerMatchHome() + $awayTeam->getNbGoalsPerMatchAway()) / 2;
        } else {
            $previsionalNbGoals = 0;
        }

        $averageGoalsHomeTeam = $this->averageHistoricForTeam($homeTeam, 'HOME');
        $averageGoalsAwayTeam = $this->averageHistoricForTeam($awayTeam, 'AWAY');

        if (empty($averageGoalsHomeTeam) || empty($averageGoalsAwayTeam)) {
            return null;
        }

        $expectedHomeGoals = $averageGoalsHomeTeam['averageHomeForceAttack']*$averageGoalsAwayTeam['averageAwayForceDefense']*$averageGoalsHomeTeam['averageChampionshipHomeGoals'];
        $expectedAwayGoals = $averageGoalsAwayTeam['averageAwayForceAttack']*$averageGoalsHomeTeam['averageHomeForceDefense']*$averageGoalsHomeTeam['averageChampionshipHomeGoals'];

        for ($homeTeamScore = 0; $homeTeamScore <= 10; $homeTeamScore++) {
            for ($awayTeamScore = 0; $awayTeamScore <= 10; $awayTeamScore++) {
                $percentage = $this->poissonDistribution($expectedHomeGoals, $expectedAwayGoals, $homeTeamScore, $awayTeamScore);
                $scoreTable[$homeTeamScore][$awayTeamScore] = $percentage;

                $totalGoals = $homeTeamScore+$awayTeamScore;
                if ($totalGoals > 2.5) {
                    $moreThanPercentage += $percentage;
                } else {
                    $lessThanPercentage += $percentage;
                }

                if ($percentage > $maxResult) {
                    $maxResult = $percentage;
                    $nbGoalsExpectedMost = $totalGoals;

                    if ($homeTeamScore > $awayTeamScore) {
                        $previsionalWinner = $homeTeam;
                    } elseif ($homeTeamScore < $awayTeamScore) {
                        $previsionalWinner = $awayTeam;
                    }
                    //else do nothing, let $previsionalWinner as null for draw
                }
            }
        }

        if ($nbGoalsExpectedMost > 2.5) {
            $myOdd = $moreThanPercentage;
        } else {
            $myOdd = $lessThanPercentage;
        }

        if (
            ($previsionalNbGoals > 2.5 && $nbGoalsExpectedMost > 2.5)
            || ($previsionalNbGoals <= 2.5 && $nbGoalsExpectedMost <= 2.5)
        ) {
            $nbGoalsIsSameAsExpected = true;
        } else {
            $nbGoalsIsSameAsExpected = false;
        }

        if ($homeTeam->getHomePlayedGames() !== 0 && $awayTeam->getAwayPlayedGames() !== 0) {
            $averageExpectedNbGoals = round((($nbGoalsExpectedMost + $previsionalNbGoals) /2), 3);
        } else {
            $averageExpectedNbGoals = $nbGoalsExpectedMost;
        }

        $game = (new Game())
                        ->setApiId($data['fixture']['id'])
                        ->setHomeTeam($homeTeam)
                        ->setAwayTeam($awayTeam)
                        ->setDate((new \DateTime($data['fixture']['date']))->modify('+ 1 hour'))
                        ->setChampionship($data['championship'])
                        ->setPrevisionalNbGoals(round($previsionalNbGoals, 3))
                        ->setExpectedNbGoals($nbGoalsExpectedMost)
                        ->setAverageExpectedNbGoals($averageExpectedNbGoals)
                        ->setPrevisionIsSameAsExpected($nbGoalsIsSameAsExpected)
                        ->setMyOdd(100/$myOdd)
                        ->setPrevisionalWinner($previsionalWinner)
        ;

        return $game;
    }

    private function poissonDistribution(float $expectedHomeGoals, float $expectedAwayGoals, int $homeScore, int $awayScore): float
    {
        return (
            ((exp(-$expectedHomeGoals)*($expectedHomeGoals ** $homeScore))/$this->factorielle($homeScore)) *
            ((exp(-$expectedAwayGoals)*($expectedAwayGoals ** $awayScore))/$this->factorielle($awayScore)) * 100
        );
    }

    private function factorielle(int $expectedGoals): int
    {
        $factorielle = 1;
        while ($expectedGoals >= 1) {
            $factorielle = $expectedGoals * $factorielle;
            $expectedGoals--;
        }

        return $factorielle;
    }

    private function averageHistoricForTeam(Team $team, string $type): array
    {
        $totalCoeff = 0;
        $averageHomeForceAttack = 0;
        $averageHomeForceDefense = 0;
        $averageAwayForceAttack = 0;
        $averageAwayForceDefense = 0;

        $averageChampionshipHomeGoals = 0;
        $averageChampionshipAwayGoals = 0;

        $teamHistoricRepository = $this->em->getRepository(TeamHistoric::class);

        /** @var TeamHistoric[] $teamHistorics */
        $teamHistorics = $teamHistoricRepository->findBy(['team' => $team], ['season' => 'ASC']);

        // 8 is current year coeff
        $currentYearCoeff = 8;
        $coeffs = [
            2017 => 1,
            2018 => 3,
            2019 => 5,
        ];

        foreach ($teamHistorics as $teamHistoric) {
            $averageHomeForceAttack += $teamHistoric->getHomeForceAttack() * $coeffs[$teamHistoric->getSeason()];
            $averageHomeForceDefense += $teamHistoric->getHomeForceDefense() * $coeffs[$teamHistoric->getSeason()];
            $averageAwayForceAttack += $teamHistoric->getAwayForceAttack() * $coeffs[$teamHistoric->getSeason()];
            $averageAwayForceDefense += $teamHistoric->getAwayForceDefense() * $coeffs[$teamHistoric->getSeason()];

            $averageChampionshipHomeGoals += $teamHistoric->getChampionshipHistoric()->getAverageGoalsHomeFor() * $coeffs[$teamHistoric->getSeason()];
            $averageChampionshipAwayGoals += $teamHistoric->getChampionshipHistoric()->getAverageGoalsAwayFor() * $coeffs[$teamHistoric->getSeason()];

            $totalCoeff += $coeffs[$teamHistoric->getSeason()];
        }

        if ('HOME' === $type) {
            if (empty($teamHistorics) && ($team->getHomePlayedGames() === 0 || $team->getHomePlayedGames() === null)) {
                return [];
            }
            if ($team->getHomePlayedGames() > 0) {
                $averageChampionshipHomeGoals += $team->getChampionship()->getAverageGoalsHomeFor() * $currentYearCoeff;
                $averageHomeForceAttack += $team->getHomeForceAttack() * $currentYearCoeff;
                $averageHomeForceDefense += $team->getHomeForceDefense() * $currentYearCoeff;

                $totalCoeff += $currentYearCoeff;
            }

            return [
                'averageHomeForceAttack' => $averageHomeForceAttack / $totalCoeff,
                'averageHomeForceDefense' => $averageHomeForceDefense / $totalCoeff,
                'averageChampionshipHomeGoals' => $averageChampionshipHomeGoals / $totalCoeff,
            ];
        } else {
            if (empty($teamHistorics) && ($team->getAwayPlayedGames() === 0 || $team->getAwayPlayedGames() === null)) {
                return [];
            }
            if ($team->getAwayPlayedGames() > 0) {
                $averageChampionshipAwayGoals += $team->getChampionship()->getAverageGoalsAwayFor() * $currentYearCoeff;
                $averageAwayForceAttack += $team->getAwayForceAttack() * $currentYearCoeff;
                $averageAwayForceDefense += $team->getAwayForceDefense() * $currentYearCoeff;

                $totalCoeff += $currentYearCoeff;
            }

            return [
                'averageAwayForceAttack' => $averageAwayForceAttack / $totalCoeff,
                'averageAwayForceDefense' => $averageAwayForceDefense / $totalCoeff,
                'averageChampionshipAwayGoals' => $averageChampionshipAwayGoals / $totalCoeff,
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return Game::class === $type;
    }
}