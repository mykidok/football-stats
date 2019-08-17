<?php

namespace App\Command;

use App\Entity\Championship;
use App\Entity\Client;
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

    public function __construct(Client $client, EntityManagerInterface $em, TeamHandler $teamHandler, ChampionshipHandler $championshipHandler)
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
            $entrypoint = sprintf('competitions/%s/standings', $championship->getApiId());
            $standings = $this->client->get($entrypoint);

            $championshipGoals = $this->championshipHandler->handleChampionshipGoals($standings);

            foreach ($standings['standings'] as $item) {
                if ('TOTAL' === $item['type']) {
                    continue;
                }

                $this->teamHandler->handleTeamUpdate($item, ['type' => $item['type']], $championshipGoals);
            }

            if ($championshipGoals['totalAwayPlayedGames'] !== 0 || $championshipGoals['totalHomePlayedGames'] !== 0) {
                $championship
                    ->setAverageGoalsAwayFor($championshipGoals['totalAwayGoalsFor']/$championshipGoals['totalAwayPlayedGames'])
                    ->setAverageGoalsAwayAgainst($championshipGoals['totalAwayGoalsAgainst']/$championshipGoals['totalAwayPlayedGames'])
                    ->setAverageGoalsHomeFor($championshipGoals['totalHomeGoalsFor']/$championshipGoals['totalHomePlayedGames'])
                    ->setAverageGoalsHomeAgainst($championshipGoals['totalHomeGoalsAgainst']/$championshipGoals['totalHomePlayedGames'])
                ;

                $this->em->persist($championship);
            }
        }

        $this->em->flush();
    }
}