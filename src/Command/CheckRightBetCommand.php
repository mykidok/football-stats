<?php

namespace App\Command;

use App\Entity\Championship;
use App\Entity\Client;
use App\Entity\Game;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckRightBetCommand extends ContainerAwareCommand
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var GameRepository
     */
    private $gameRepository;

    public function __construct(Client $client, EntityManagerInterface $em, GameRepository $gameRepository)
    {
        parent::__construct('api:check:bet');
        $this->setDescription('Check results of the day to check if bets were right');

        $this->client = $client;
        $this->em = $em;
        $this->gameRepository = $gameRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $championshipRepository = $this->em->getRepository(Championship::class);
        $championships = $championshipRepository->findAll();

        /** @var Championship $championship */
        foreach ($championships as $championship) {
            $gameDay = $this->client->get('matches', [
                    'query' => [
                        'competitions' => $championship->getApiId(),
                        'dateTo' => (new \DateTime('now'))->format('Y-m-d'),
                        'dateFrom' => (new \DateTime('now'))->format('Y-m-d'),
                    ]
                ]
            );

            if (empty($gameDay['matches'])) {
                $output->writeln(sprintf('------ No match today for %s ------', $championship->getName()));
                continue;
            }

            $i = 0;
            foreach ($gameDay['matches'] as $item) {
                if ('FINISHED' === $item['status']) {
                    /** @var Game $game */
                    $game = $this->gameRepository->findOneBy(['apiId' => $item['id']]);
                    $realNbGoals = $item['score']['fullTime']['homeTeam'] + $item['score']['fullTime']['awayTeam'];
                    if (($game->getPrevisionalNbGoals() > Game::LIMIT && $realNbGoals > Game::LIMIT)
                        || $game->getPrevisionalNbGoals() <= Game::LIMIT && $realNbGoals <= Game::LIMIT ) {
                        $game->setGoodResult(true);
                    } else {
                        $game->setGoodResult(false);
                    }

                    $game->setRealNbGoals($realNbGoals);
                    $this->em->persist($game);
                    $this->em->flush();
                    $i++;
                }
            }
            $output->writeln(sprintf('------ %d matches updated for %s ------', $i, $championship->getName()));
        }
    }

}