<?php

namespace App\Command;

use App\Entity\Game;
use App\Entity\OddsClient;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportOddsForGamesOfTheDay extends ContainerAwareCommand
{
    /**
     * @var OddsClient
     */
    private $client;

    /**
     * @var GameRepository
     */
    private $gameRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(GameRepository $gameRepository, EntityManagerInterface $em, OddsClient $client)
    {
        parent::__construct('api:import:odds');
        $this->setDescription('Import all odds for games of the day');

        $this->gameRepository = $gameRepository;
        $this->em = $em;
        $this->client = $client;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $now = (new \DateTime('now'))->format('Ymd');
        $entrypoint = sprintf('1n2/offre/%s', $now);
        $odds = $this->client->get($entrypoint, [
            'query' => [
                'sport' => '100',
            ]
        ]);

        $memo = [];
        foreach ($odds as $datum) {
            foreach ((array) $datum['formules'] as $formule) {
                if ($formule['marketType'] === "Plus/Moins 2,5 buts (Temps réglementaire)") {
                    $memo[] = $formule;
                }
            }
        }

        foreach ($memo as $bet) {
            $homeTeamName = explode('-', $bet['label'])[0];

            /** @var Game $game */
            $game = $this->gameRepository->findOneByHomeTeamShortName(new \DateTime('now'), $homeTeamName);

            if (null === $game) {
                continue;
            }

            if ($game->getPrevisionalNbGoals() <= Game::LIMIT) {
                $odd = str_replace(',', '.', $bet['outcomes'][1]['cote']);
                $game->setOdd($odd);
            } elseif ($game->getPrevisionalNbGoals() > Game::LIMIT) {
                $odd = str_replace(',', '.', $bet['outcomes'][0]['cote']);
                $game->setOdd($odd);
            }

            $output->writeln(sprintf('%s - %s updated',
                $game->getHomeTeam()->getName(),
                $game->getAwayTeam()->getName()
                )
            );
            $this->em->persist($game);
        }

        $this->em->flush();


    }
}