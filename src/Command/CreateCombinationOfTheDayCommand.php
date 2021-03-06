<?php

namespace App\Command;

use App\Entity\Bet;
use App\Entity\Championship;
use App\Entity\Combination;
use App\Entity\Game;
use App\Entity\UnderOverBet;
use App\Manager\GameManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCombinationOfTheDayCommand extends Command
{
    private $em;
    private $gameManager;

    public function __construct(EntityManagerInterface $em, GameManager $gameManager)
    {
        parent::__construct('api:create:combination');
        $this->setDescription('Check results of the day to check if bets were right');

        $this->em = $em;
        $this->gameManager = $gameManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->gameManager->setPercentageForGamesOfTheDay();

        $betRepository = $this->em->getRepository(Bet::class);
        $bets = $betRepository->findBetsOfTheDayOrderByOddAndPercentage();

        if (count($bets) < 15) {
            return $output->writeln('Not enough games today to create combination');
        }

        $combination = new Combination();
        $combination->setDate(new \DateTime('now'));

        $addedBet = null;
        foreach ($bets as $bet) {
            /** @var Bet|null $betToAdd */
            $betToAdd = $betRepository->find($bet['id']);

            if (null !== $betToAdd) {
                if ($combination->getBets()->count() === 2) {
                    continue;
                }

                if (null !== $addedBet && $betToAdd->getGame() === $addedBet->getGame()) {
                    continue;
                }

                $combination->addBet($betToAdd);
                $addedBet = $betToAdd;
            }
        }

        if ($combination->getBets()->count() < 2) {
            return $output->writeln('Not enough bets today to create combination');
        }

        foreach ($combination->getBets() as $bet) {
            if (null === $combinationOdd = $combination->getGeneralOdd()) {
                $combination->setGeneralOdd($bet->getOdd() * Combination::BET_AMOUNT);
            } else {
                $combination->setGeneralOdd($combinationOdd * $bet->getOdd());
            }
        }

        $this->em->persist($combination);
        $this->em->flush();

        return $output->writeln('Combination created');
    }
}