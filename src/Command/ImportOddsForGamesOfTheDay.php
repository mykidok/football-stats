<?php

namespace App\Command;

use App\Entity\OddsClient;
use App\Manager\GameManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportOddsForGamesOfTheDay extends Command
{
    private $client;
    private $gameManager;

    public function __construct(GameManager $gameManager, OddsClient $client)
    {
        parent::__construct('api:import:odds');
        $this->setDescription('Import all odds for games of the day');

        $this->client = $client;
        $this->gameManager = $gameManager;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $odds = $this->client->get(sprintf('1n2/offre/%s', (new \DateTime('now'))->format('Ymd')), [
            'query' => [
                'sport' => '100',
            ]
        ]);

        $clientOdds = [];
        foreach ($odds as $data) {
            foreach ((array) $data['formules'] as $formule) {
                if ($formule['marketType'] === "Plus/Moins 2,5 buts (Temps Réglementaire)") {
                    $clientOdds[] = array_merge($formule, ['winnerOdds' => $data['outcomes']]);
                }
            }
        }

        $games = $this->gameManager->setOddsForGamesOfTheDay($clientOdds);

        foreach ($games as $game) {
            $output->writeln(
                sprintf('%s - %s updated', $game->getHomeTeam()->getName(), $game->getAwayTeam()->getName())
            );
        }
    }
}