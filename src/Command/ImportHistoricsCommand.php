<?php

namespace App\Command;

use App\Entity\Championship;
use App\Entity\ChampionshipHistoric;
use App\Entity\Client;
use App\Entity\Team;
use App\Entity\TeamHistoric;
use App\Handler\ChampionshipHandler;
use App\Handler\TeamHistoricHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportHistoricsCommand extends Command
{
    private $client;
    private $em;
    private $championshipHandler;
    private $teamHistoricHandler;

    public function __construct(
        Client $client,
        EntityManagerInterface $em,
        ChampionshipHandler $championshipManager,
        TeamHistoricHandler $teamHistoricHandler
    )
    {
        parent::__construct('api:import:historics');
        $this->setDescription('Import historics from API Football Data');

        $this->client = $client;
        $this->em = $em;
        $this->championshipHandler = $championshipManager;
        $this->teamHistoricHandler = $teamHistoricHandler;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $teamRepository = $this->em->getRepository(Team::class);
        $championshipRepository = $this->em->getRepository(Championship::class);
        $championshipHistoricRepository = $this->em->getRepository(ChampionshipHistoric::class);
        $teamHistoricRepository = $this->em->getRepository(TeamHistoric::class);

        /** @var Championship $championship */
        $championships = $championshipRepository->findAll();

        $year = (int) (new \DateTime())->format('Y') - 1;
        foreach ($championships as $championship) {
            for ($year; $year >= 2017; $year--) {
                $standings = $this->client->get('standings', [
                    'query' => [
                        'league' => $championship->getApiId(),
                        'season' => $year,
                    ]
                ]);

                if (empty($standings['response'])) {
                    continue;
                }
                $championshipGoals = $this->championshipHandler->handleChampionshipGoals($standings['response'][0]);


                if (null === ($championshipHistoric = $championshipHistoricRepository->findOneBy(['season' => $year, 'championship' => $championship]))) {
                    $championshipHistoric = (new ChampionshipHistoric())
                        ->setChampionship($championship)
                        ->setSeason($year)
                        ->setAverageGoalsAwayFor($championshipGoals['totalAwayGoalsFor']/$championshipGoals['totalAwayPlayedGames'])
                        ->setAverageGoalsAwayAgainst($championshipGoals['totalAwayGoalsAgainst']/$championshipGoals['totalAwayPlayedGames'])
                        ->setAverageGoalsHomeFor($championshipGoals['totalHomeGoalsFor']/$championshipGoals['totalHomePlayedGames'])
                        ->setAverageGoalsHomeAgainst($championshipGoals['totalHomeGoalsAgainst']/$championshipGoals['totalHomePlayedGames'])
                    ;

                    $this->em->persist($championshipHistoric);
                }


                $teamsHistorics = $this->teamHistoricHandler->handleTeamsHistorics($standings['response'][0]);

                foreach ($teamsHistorics as $apiId => $historic) {
                    /** @var Team|null $team */
                    $team = $teamRepository->findOneBy(['apiId' => $apiId]);

                    if (null === $team) {
                        continue;
                    }

                    if (null === $teamHistoricRepository->findOneBy(['season' => $year, 'team' => $team])) {
                        $teamHistoric = (new TeamHistoric())
                            ->setSeason($year)
                            ->setChampionshipHistoric($championshipHistoric)
                            ->setTeam($team)
                            ->setHomeForceAttack(($historic['homeGoalsFor']/$historic['homePlayedGames'])/($championshipGoals['totalHomeGoalsFor']/$championshipGoals['totalHomePlayedGames']))
                            ->setHomeForceDefense(($historic['homeGoalsAgainst']/$historic['homePlayedGames'])/($championshipGoals['totalHomeGoalsAgainst']/$championshipGoals['totalHomePlayedGames']))
                            ->setAwayForceAttack(($historic['awayGoalsFor']/$historic['awayPlayedGames'])/($championshipGoals['totalAwayGoalsFor']/$championshipGoals['totalAwayPlayedGames']))
                            ->setAwayForceDefense(($historic['awayGoalsAgainst']/$historic['awayPlayedGames'])/($championshipGoals['totalAwayGoalsAgainst']/$championshipGoals['totalAwayPlayedGames']))
                        ;

                        $this->em->persist($teamHistoric);
                    }
                }

                $this->em->flush();
                sleep(6);
            }
        }
    }
}