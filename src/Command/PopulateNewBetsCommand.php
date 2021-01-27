<?php


namespace App\Command;


use App\Entity\Combination;
use App\Entity\Game;
use App\Entity\UnderOverBet;
use App\Entity\WinnerBet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateNewBetsCommand extends Command
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct('api:populate:bets');
        $this
            ->setDescription('Populate new bets from old ones');

        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $combinationRepository = $this->em->getRepository(Combination::class);

        foreach ($combinationRepository->findAll() as $combination) {
            /** @var Game $combinationGame */
            foreach ($combination->getGames() as $combinationGame) {
                $newCombination = (new Combination())
                    ->setGeneralOdd($combination->getGeneralOdd())
                    ->setSuccess($combination->isSuccess())
                    ->setDate($combination->getDate());

                foreach ($combinationGame->getBets() as $bet) {
                    if ($combinationGame->isBetOnWinner() && $bet instanceof WinnerBet) {
                        $newCombination->addBet($bet);
                    }

                    if (!$combinationGame->isBetOnWinner() && $bet instanceof UnderOverBet){
                        $newCombination->addBet($bet);
                    }
                }
            }

            if ($newCombination->getBets()->count() === 2) {
                $this->em->persist($newCombination);
            }
        }

        $this->em->flush();
    }

}