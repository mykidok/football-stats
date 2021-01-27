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
        $gameRepository = $this->em->getRepository(Game::class);

        /** @var Game $game */
        foreach ($gameRepository->findAll() as $game) {
            $type = $game->getAverageExpectedNbGoals() <= UnderOverBet::LIMIT_2_5 ? UnderOverBet::LESS_TWO_AND_A_HALF : UnderOverBet::PLUS_TWO_AND_A_HALF;
            $underOverBet = (new UnderOverBet())
                ->setGoodResult($game->isGoodResult())
                ->setType($type)
                ->setForm($game->isMomentForm())
                ->setOdd($game->getOdd())
                ->setPercentage($game->getPercentage())
                ->setPrevisionIsSameAsExpected($game->isPrevisionIsSameAsExpected())
            ;

            $winnerBet = (new WinnerBet())
                ->setMyOdd($game->getMyOdd())
                ->setPercentage($game->getWinnerPercentage())
                ->setForm($game->getWinnerMomentForm())
                ->setOdd($game->getWinnerOdd())
                ->setGoodResult($game->getWinnerResult())
                ->setWinner($game->getPrevisionalWinner())
                ->setType(WinnerBet::WINNER_TYPE);

            $game->addBet($underOverBet)->addBet($winnerBet);

            $this->em->persist($game);
        }

        $this->em->flush();

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
                    } else {
                        $newCombination->addBet($bet);
                    }
                }

                $this->em->persist($newCombination);
            }
        }

        $this->em->flush();
    }

}