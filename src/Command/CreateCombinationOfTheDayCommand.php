<?php

namespace App\Command;


use App\Entity\Combination;
use App\Entity\Game;
use App\Entity\Team;
use App\Manager\GameManager;
use App\Repository\ChampionshipRepository;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCombinationOfTheDayCommand extends Command
{
    private $em;
    private $gameRepository;
    private $championshipRepository;
    private $gameManager;

    public function __construct(EntityManagerInterface $em, GameRepository $gameRepository, ChampionshipRepository $championshipRepository, GameManager $gameManager)
    {
        parent::__construct('api:create:combination');
        $this->setDescription('Check results of the day to check if bets were right');

        $this->em = $em;
        $this->gameRepository = $gameRepository;
        $this->championshipRepository = $championshipRepository;
        $this->gameManager = $gameManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $teams = $this->championshipRepository->findTeamsWithStatistics();

        $this->gameManager->setPercentageForGamesOfTheDay($teams);

        /** @var Game[] $games */
        $games = $this->gameRepository->findGamesOfTheDayOrderByOddAndPercentage(new \DateTime('now'));

        if (count($games) < 5) {
            return $output->writeln('Not enough games today to create combination');
        }

        $combination = new Combination();
        $combination->setDate(new \DateTime('now'));

        for ($i = 0; $i < 2; $i++) {
            $combination->addGame($games[$i]);
        }

        /** @var Game $game */
        foreach ($combination->getGames() as $game) {
            if (null === $combinationOdd = $combination->getGeneralOdd()) {
                $combination->setGeneralOdd($game->getOdd() * Combination::BET_AMOUNT);
            } else {
                $combination->setGeneralOdd($combinationOdd * $game->getOdd());
            }
        }

        $this->em->persist($combination);
        $this->em->flush();

        $output->writeln('Combination created');
    }
}