<?php

namespace App\Command;


use App\Entity\Combination;
use App\Entity\Game;
use App\Repository\ChampionshipRepository;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCombinationOfTheDayCommand extends Command
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
     * @var ChampionshipRepository
     */
    private $championshipRepository;

    public function __construct(EntityManagerInterface $em, GameRepository $gameRepository, ChampionshipRepository $championshipRepository)
    {
        parent::__construct('api:create:combination');
        $this->setDescription('Check results of the day to check if bets were right');

        $this->em = $em;
        $this->gameRepository = $gameRepository;
        $this->championshipRepository = $championshipRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $teams = $this->championshipRepository->findTeamsWithStatistics();

        /** @var Game[] $games */
        $games = $this->gameRepository->findGamesOfTheDayOrderByOdd(new \DateTime('now'));

        if (count($games) < 6) {
            return $output->writeln('Not enough games today to create combination');
        }

        $combination = new Combination();
        $combination->setDate(new \DateTime('now'));

        for ($i = 0; $i <= 2; $i++) {
            foreach ($teams as $team) {
                if ($team['teamName'] === $games[$i]->getHomeTeam()->getName()) {
                    $combination->addGame($games[$i]);
                    if (null === $combinationOdd = $combination->getGeneralOdd()) {
                        $combination->setGeneralOdd($games[$i]->getOdd() * Combination::BET_AMOUNT);
                    } else {
                        $combination->setGeneralOdd($combinationOdd * $games[$i]->getOdd());
                    }
                }
            }
        }

        $this->em->persist($combination);
        $this->em->flush();

        $output->writeln('Combination created');
    }
}