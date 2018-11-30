<?php

namespace App\Command;

use App\Entity\Combination;
use App\Entity\Game;
use App\Repository\CombinationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCombinationResultCommand extends Command
{
    /**
     * @var CombinationRepository
     */
    private $combinationRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em, CombinationRepository $combinationRepository)
    {
        parent::__construct('api:check:combination');
        $this->setDescription('Check results of the day to check if combination was right');

        $this->em = $em;
        $this->combinationRepository = $combinationRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Combination|null $lastCombination */
        $lastCombination = $this->combinationRepository->findCombinationOfTheDay(new \DateTime('1 day ago'));

        if (null === $lastCombination) {
            return $output->writeln('No combination yesterday');
        }

        $i = 0;
        /** @var Game $game */
        foreach ($lastCombination->getGames() as $game) {
            if ($game->isGoodResult()) {
                $i++;
            }
        }

        if ($i !== $lastCombination->getGames()->count()) {
            $lastCombination->setSuccess(false);
        } else {
            $lastCombination->setSuccess(true);
        }

        $this->em->persist($lastCombination);
        $this->em->flush();

        $output->writeln('Combination updated !');
    }
}