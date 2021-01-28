<?php

namespace App\Command;

use App\Entity\Championship;
use App\Entity\Client;
use App\Entity\DataClient;
use App\Handler\ChampionshipHandler;
use App\Handler\TeamHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateTeamGoalsCommand extends Command
{
    private $client;
    private $em;
    private $teamHandler;
    private $championshipHandler;

    public function __construct(DataClient $client, EntityManagerInterface $em, TeamHandler $teamHandler, ChampionshipHandler $championshipHandler)
    {
        parent::__construct('api:update:teams');
        $this->setDescription('Update team away and home goals');

        $this->client = $client;
        $this->em = $em;
        $this->teamHandler = $teamHandler;
        $this->championshipHandler = $championshipHandler;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $championshipRepository = $this->em->getRepository(Championship::class);
        $championships = $championshipRepository->findAll();

        /** @var Championship $championship */
        foreach ($championships as $championship) {
            $standings = $this->client->get('standings', [
                'query' => [
                    'league' => $championship->getApiId(),
                    'season' => 2020,
                ]
            ]);

            if (empty($standings['response'])) {
                continue;
            }

            $championshipGoals = $this->championshipHandler->handleChampionshipGoals($standings['response'][0]);

            foreach ($standings['response'][0] as $standing) {
                $this->teamHandler->handleTeamUpdate($standing, $championshipGoals);
            }

            if ($championshipGoals['totalAwayPlayedGames'] !== 0 || $championshipGoals['totalHomePlayedGames'] !== 0) {
                $championship
                    ->setAverageGoalsAwayFor($championshipGoals['totalAwayGoalsFor']/$championshipGoals['totalAwayPlayedGames'])
                    ->setAverageGoalsAwayAgainst($championshipGoals['totalAwayGoalsAgainst']/$championshipGoals['totalAwayPlayedGames'])
                    ->setAverageGoalsHomeFor($championshipGoals['totalHomeGoalsFor']/$championshipGoals['totalHomePlayedGames'])
                    ->setAverageGoalsHomeAgainst($championshipGoals['totalHomeGoalsAgainst']/$championshipGoals['totalHomePlayedGames'])
                ;

                $this->em->persist($championship);
                $output->writeln(sprintf('------ Teams data updated for %s ------', $championship->getName()));
            }
        }

        $this->em->flush();
    }
}