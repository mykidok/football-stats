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
                foreach ($combinationGame->getBets() as $bet) {
                    if ($combinationGame->isBetOnWinner() && $bet instanceof WinnerBet) {
                        $combination->addBet($bet);
                        continue;
                    }

                    if (!$combinationGame->isBetOnWinner() && $bet instanceof UnderOverBet){
                        $combination->addBet($bet);
                    }
                }
            }

            $this->em->persist($combination);
        }

        $this->em->flush();
    }

}