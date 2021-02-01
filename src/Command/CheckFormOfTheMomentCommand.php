<?php

namespace App\Command;

use App\Entity\Game;
use App\Entity\Team;
use App\Manager\BetManager;
use App\Manager\GameManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckFormOfTheMomentCommand extends Command
{
    private $em;
    private $betManager;
    private $gameManager;

    public function __construct(EntityManagerInterface $em, BetManager $betManager, GameManager $gameManager)
    {
        parent::__construct('api:check:form');
        $this->setDescription('Check form of the moment for each match of the day');

        $this->em = $em;
        $this->betManager = $betManager;
        $this->gameManager = $gameManager;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gameRepository = $this->em->getRepository(Game::class);
        $games = $gameRepository->findGamesOfTheDay(new \DateTime());

        if (empty($games)) {
            $output->writeln('No games played today');
        }

        $teamRepository = $this->em->getRepository(Team::class);
        $teamsOfTheDay = $teamRepository->findTeamsWithGamesToday();

        /** @var Team $team */
        foreach ($teamsOfTheDay as $team) {
            /** @var Game[] $lastGames */
            $lastGames = $gameRepository->findLastFourGamesForTeam($team);

            if (count($lastGames) === 0) {
                continue;
            }

            $goals = 0;
            $points = 0;
            foreach ($lastGames as $lastGame) {
                $goals += $lastGame->getRealNbGoals();
                if (null === $lastGame->getWinner()) {
                    $points += 1;
                } elseif ($lastGame->getWinner()->getId() === $team->getId()) {
                    $points += 3;
                }
            }

            $team->setMomentForm($goals/count($lastGames));
            $team->setPointsMomentForm($points);
            $this->em->persist($team);
        }

        $this->em->flush();

        /** @var Game $game */
        foreach ($games as $game) {
            if (null ===  $formForMatch = $this->gameManager->getFormForMatch($game)) {
                continue;
            }

            foreach ($game->getBets() as $bet) {
                $bet->setForm($this->betManager->getFormOfTheMomentForBet($game, $bet, $formForMatch));
                $this->em->persist($bet);
            }

            $this->em->persist($game);
            $output->writeln(sprintf('%s - %s updated', $game->getHomeTeam()->getName(), $game->getAwayTeam()->getName()));
        }

        $this->em->flush();
    }


}