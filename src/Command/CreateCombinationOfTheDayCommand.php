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
        $myOdd = null;
        $percentage = null;
        $odd = null;
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

                $odd = null === $odd ? $betToAdd->getOdd() : $odd * $betToAdd->getOdd();
                $myOdd = null === $myOdd ? $betToAdd->getMyOdd() : $myOdd * $betToAdd->getMyOdd();
                $percentage = null === $percentage ? $betToAdd->getPercentage() : ($percentage + $betToAdd->getPercentage()) / 2;
            }
        }

        if ($combination->getBets()->count() < 2) {
            return $output->writeln('Not enough bets today to create combination');
        }


        $finalPercentage = (1/$myOdd + ($percentage / 100)) / 2;
        $kellyCriterion = (($finalPercentage * ($odd - 1)) - (1 - $finalPercentage)) / ($odd - 1);

        $combinationRepository = $this->em->getRepository(Combination::class);
        $amount = 0;
        /** @var Combination $finishedCombination */
        foreach ($combinationRepository->findCombinationFinished() as $finishedCombination) {
            $amount = $finishedCombination->isSuccess() ? $amount + ($finishedCombination->getGeneralOdd() - $finishedCombination->getBet()) : $amount - $finishedCombination->getBet();
        }

        // to divide by 5 to not bet more than 20%
        $combination->setBet( round(($amount / 5) / (1 / $kellyCriterion)));
        foreach ($combination->getBets() as $bet) {
            if (null === $combinationOdd = $combination->getGeneralOdd()) {
                $combination->setGeneralOdd($bet->getOdd() * $combination->getBet());
            } else {
                $combination->setGeneralOdd($combinationOdd * $bet->getOdd());
            }
        }

        $this->em->persist($combination);
        $this->em->flush();

        return $output->writeln('Combination created');
    }
}