<?php

namespace App\Command;

use App\Entity\Combination;
use App\Entity\Game;
use App\Entity\Payroll;
use App\Repository\CombinationRepository;
use App\Repository\PayrollRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCombinationResultCommand extends Command
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct('api:check:combination');
        $this->setDescription('Check results of the day to check if combination was right');

        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var CombinationRepository $combinationRepository */
        $combinationRepository = $this->em->getRepository(Combination::class);
        /** @var Combination|null $lastCombination */
        $lastCombination = $combinationRepository->findCombinationOfTheDay(new \DateTime('1 day ago'));

        if (null === $lastCombination) {
            return $output->writeln('No combination yesterday');
        }

        $i = 0;
        /** @var Game $game */
        foreach ($lastCombination->getBets() as $bet) {
            if (null === $bet->isGoodResult()) {
                return $output->writeln('At least one match has not been played.');
            }
            if ($bet->isGoodResult()) {
                $i++;
            }
        }

        /** @var PayrollRepository $payrollRepository */
        $payrollRepository = $this->em->getRepository(Payroll::class);
        /** @var Payroll $lastPayroll */
        $lastPayroll = $payrollRepository->findLastPayroll()[0];

        if ($i !== $lastCombination->getBets()->count()) {
            $lastCombination->setSuccess(false);
            $amount = $lastPayroll->getAmount() - $lastCombination->getBet();
        } else {
            $lastCombination->setSuccess(true);
            $amount = $lastPayroll->getAmount() + ($lastCombination->getGeneralOdd() - $lastCombination->getBet());
        }

        $payroll = (new Payroll())
            ->setDate(new \DateTime('1 day ago'))
            ->setAmount($amount)
        ;

        $this->em->persist($lastCombination);
        $this->em->persist($payroll);
        $this->em->flush();

        $output->writeln('Combination updated !');
    }
}