<?php

namespace App\Command;


use App\Entity\Game;
use App\Entity\Team;
use App\Repository\GameRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckFormOfTheMomentCommand extends ContainerAwareCommand
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

        $teamsWithForm = $this->teamRepository->findTeamWithFormOfTheMoment($now);

        foreach ($teamsWithForm as $team) {
            /** @var Team $teamToUpdate */
            $teamToUpdate = $this->teamRepository->findOneBy(['apiId' => $team['apiId']]);
            $teamToUpdate->setMomentForm($team['momentForm']);
            $this->em->persist($teamToUpdate);
        }

        $this->em->flush();

        /** @var Game $game */
        foreach ($games as $game) {
            $homeTeamForm = null;
            $awayTeamForm = null;
            $homeTeam = $game->getHomeTeam();
            $awayTeam = $game->getAwayTeam();
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