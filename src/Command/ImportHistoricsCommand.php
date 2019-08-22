<?php

namespace App\Command;

use App\Entity\Championship;
use App\Entity\ChampionshipHistoric;
use App\Entity\Client;
use App\Entity\Team;
use App\Entity\TeamHistoric;
use App\Handler\ChampionshipHandler;
use App\Handler\TeamHistoricHandler;
use App\Repository\TeamRepository;
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
    private $teamRepository;

    public function __construct(
        Client $client,
        EntityManagerInterface $em,
        ChampionshipHandler $championshipHandler,
        TeamHistoricHandler $teamHistoricHandler,
        TeamRepository $teamRepository
    )
    {
        parent::__construct('api:import:historics');
        $this->setDescription('Import historics from API Football Data');

        $this->client = $client;
        $this->em = $em;
        $this->championshipHandler = $championshipHandler;
        $this->teamHistoricHandler = $teamHistoricHandler;
        $this->teamRepository = $teamRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $championshipRepository = $this->em->getRepository(Championship::class);

        /** @var Championship $championship */
        $championships = $championshipRepository->findAll();

        foreach ($championships as $championship) {
            for ($i = 2018; $i >= 2017; $i--) {
                $entrypoint = sprintf('competitions/%d/standings', $championship->getApiId());
                $standings = $this->client->get($entrypoint, [
                    'query' => [
                        'season' => $i,
                    ]
                ]);

                $championshipGoals = $this->championshipHandler->handleChampionshipGoals($standings);

                $championshipHistoric = (new ChampionshipHistoric())
                    ->setChampionship($championship)
                    ->setSeason($i)
                    ->setAverageGoalsAwayFor($championshipGoals['totalAwayGoalsFor']/$championshipGoals['totalAwayPlayedGames'])
                    ->setAverageGoalsAwayAgainst($championshipGoals['totalAwayGoalsAgainst']/$championshipGoals['totalAwayPlayedGames'])
                    ->setAverageGoalsHomeFor($championshipGoals['totalHomeGoalsFor']/$championshipGoals['totalHomePlayedGames'])
                    ->setAverageGoalsHomeAgainst($championshipGoals['totalHomeGoalsAgainst']/$championshipGoals['totalHomePlayedGames'])
                ;

                $this->em->persist($championshipHistoric);

                $teamsHistorics = $this->teamHistoricHandler->handleTeamsHistorics($standings);

                foreach ($teamsHistorics as $apiId => $historic) {
                    /** @var Team|null $team */
                    $team = $this->teamRepository->findOneBy(['apiId' => $apiId]);

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

                    $this->em->persist($teamHistoric);
                }

                $this->em->flush();
            }
            sleep(20);
        }

    }
}