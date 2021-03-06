<?php

namespace App\Command;

use App\Entity\Championship;
use App\Entity\ChampionshipHistoric;
use App\Entity\Client;
use App\Entity\Team;
use App\Entity\TeamHistoric;
use App\Handler\ChampionshipHandler;
use App\Handler\TeamHistoricHandler;
use Doctrine\DBAL\Exception\ConstraintViolationException;
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

        /** @var Championship $championship */
        $championships = $championshipRepository->findAll();

        foreach ($championships as $championship) {
            for ($i = 2019; $i >= 2017; $i--) {
                $standings = $this->client->get('standings', [
                    'query' => [
                        'league' => $championship->getApiId(),
                        'season' => $i,
                    ]
                ]);

                if (empty($standings['response'])) {
                    continue;
                }
                $championshipGoals = $this->championshipHandler->handleChampionshipGoals($standings['response'][0]);

                $championshipHistoric = (new ChampionshipHistoric())
                    ->setChampionship($championship)
                    ->setSeason($i)
                    ->setAverageGoalsAwayFor($championshipGoals['totalAwayGoalsFor']/$championshipGoals['totalAwayPlayedGames'])
                    ->setAverageGoalsAwayAgainst($championshipGoals['totalAwayGoalsAgainst']/$championshipGoals['totalAwayPlayedGames'])
                    ->setAverageGoalsHomeFor($championshipGoals['totalHomeGoalsFor']/$championshipGoals['totalHomePlayedGames'])
                    ->setAverageGoalsHomeAgainst($championshipGoals['totalHomeGoalsAgainst']/$championshipGoals['totalHomePlayedGames'])
                ;

                try {
                    $this->em->persist($championshipHistoric);
                } catch(ConstraintViolationException $e) {
                    // do nothing
                }

                $teamsHistorics = $this->teamHistoricHandler->handleTeamsHistorics($standings['response'][0]);

                foreach ($teamsHistorics as $apiId => $historic) {
                    /** @var Team|null $team */
                    $team = $teamRepository->findOneBy(['apiId' => $apiId]);

                    if (null === $team) {
                        continue;
                    }

                    $teamHistoric = (new TeamHistoric())
                        ->setSeason($i)
                        ->setChampionshipHistoric($championshipHistoric)
                        ->setTeam($team)
                        ->setHomeForceAttack(($historic['homeGoalsFor']/$historic['homePlayedGames'])/($championshipGoals['totalHomeGoalsFor']/$championshipGoals['totalHomePlayedGames']))
                        ->setHomeForceDefense(($historic['homeGoalsAgainst']/$historic['homePlayedGames'])/($championshipGoals['totalHomeGoalsAgainst']/$championshipGoals['totalHomePlayedGames']))
                        ->setAwayForceAttack(($historic['awayGoalsFor']/$historic['awayPlayedGames'])/($championshipGoals['totalAwayGoalsFor']/$championshipGoals['totalAwayPlayedGames']))
                        ->setAwayForceDefense(($historic['awayGoalsAgainst']/$historic['awayPlayedGames'])/($championshipGoals['totalAwayGoalsAgainst']/$championshipGoals['totalAwayPlayedGames']))
                    ;

                    try {
                        $this->em->persist($teamHistoric);
                    } catch (ConstraintViolationException $e) {
                        continue;
                    }
                }

                $this->em->flush();
                sleep(6);
            }
        }
    }
}