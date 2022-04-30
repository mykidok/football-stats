<?php

namespace App\Command;

use App\Entity\Bet;
use App\Entity\Combination;
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

        $combination = new Combination();
        $combination->setDate(new \DateTime('now'));

        foreach ($bets as $bet) {
            /** @var Bet|null $betToAdd */
            $betToAdd = $betRepository->find($bet['id']);

            if (null !== $betToAdd) {
                if ($combination->getBets()->count() === 1) {
                    continue;
                }

                $combination->addBet($betToAdd);

                $odd = $betToAdd->getOdd();
                $myOdd = $betToAdd->getMyOdd();
                $percentage =$betToAdd->getPercentage();
            }
        }

        if ($combination->getBets()->count() === 0) {
            return $output->writeln('Not enough bets today to create combination');
        }


        $finalPercentage = (1/$myOdd + ($percentage / 100)) / 2;
        $kellyCriterion = (($finalPercentage * ($odd - 1)) - (1 - $finalPercentage)) / ($odd - 1);

        $combinationRepository = $this->em->getRepository(Combination::class);
        $amount = 50;
        /** @var Combination $finishedCombination */
        foreach ($combinationRepository->findCombinationFinished() as $finishedCombination) {
            $amount = $finishedCombination->isSuccess() ? $amount + ($finishedCombination->getGeneralOdd() - $finishedCombination->getBet()) : $amount - $finishedCombination->getBet();
        }

        // to divide by 2 to not bet more than 50%
        $combinationBet = round(($amount / 2) / (1 / $kellyCriterion));

        if ($combinationBet < 1) {
            return $output->writeln('No combination created because no chances');
        }

        $combination->setBet($combinationBet);
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