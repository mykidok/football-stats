<?php

namespace App\Command;


use App\Entity\Game;
use App\Entity\Team;
use App\Repository\GameRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckFormOfTheMomentCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var GameRepository
     */
    private $gameRepository;

    /**
     * @var TeamRepository
     */
    private $teamRepository;

    public function __construct(EntityManagerInterface $em, GameRepository $gameRepository, TeamRepository $teamRepository)
    {
        parent::__construct('api:check:form');
        $this->setDescription('Check form of the moment for each match of the day');

        $this->em = $em;
        $this->gameRepository = $gameRepository;
        $this->teamRepository = $teamRepository;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $now = new \DateTime('now');
        $games = $this->gameRepository->findGamesOfTheDay($now);

        if (empty($games)) {
            $output->writeln('No games played today');
        }

        $teamsOfTheDay = $this->teamRepository->findTeamsWithGamesToday($now);

        /** @var Team $team */
        foreach ($teamsOfTheDay as $team) {
            $lastGames = $this->gameRepository->findLastFourGamesForTeam($team);

            $goals = 0;
            foreach ($lastGames as $lastGame) {
                $goals = $goals + $lastGame['realNbGoals'];
            }

            $team->setMomentForm($goals/count($lastGames));
            $this->em->persist($team);
        }

        $this->em->flush();

        /** @var Game $game */
        foreach ($games as $game) {
            $homeTeamForm = null;
            $awayTeamForm = null;
            if (null !== $game->getHomeTeam()->getMomentForm()) {
                $homeTeamForm = $game->getHomeTeam()->getMomentForm();
            }

            if (null !== $game->getAwayTeam()->getMomentForm()){
                $awayTeamForm = $game->getAwayTeam()->getMomentForm();
            }

            if (null !== $awayTeamForm && null !== $homeTeamForm) {
                $formForMatch = ($game->getHomeTeam()->getMomentForm() + $game->getAwayTeam()->getMomentForm())/2;
            } elseif (null === $homeTeamForm && null !== $awayTeamForm) {
                $formForMatch = $game->getAwayTeam()->getMomentForm();
            } elseif (null === $awayTeamForm && null !== $homeTeamForm) {
                $formForMatch = $game->getHomeTeam()->getMomentForm();
            } else {
                continue;
            }

            if (($formForMatch > Game::LIMIT && $game->getPrevisionalNbGoals() > Game::LIMIT)
            || ($formForMatch < Game::LIMIT && $game->getPrevisionalNbGoals() < Game::LIMIT)) {
                $game->setMomentForm(true);
            } else {
                $game->setMomentForm(false);
            }

            $this->em->persist($game);
            $output->writeln(sprintf('%s - %s updated',
                    $game->getHomeTeam()->getName(),
                    $game->getAwayTeam()->getName()));
        }

        $this->em->flush();
    }


}