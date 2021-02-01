<?php

namespace App\Controller;

use App\Entity\Championship;
use App\Entity\Combination;
use App\Entity\Game;
use App\Form\Type\DateType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("", name="home")
     */
    public function homeAction()
    {
        return $this->render('home.html.twig');
    }

    /**
     * @Route("/bets", name="bets", methods="GET|POST")
     * @Template(template="bets.html.twig")
     */
    public function bets(Request $request)
    {
        $form = $this->get('form.factory')->create(DateType::class);

        $date = new \DateTime();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $date = $form->get('date')->getData();
        }

        $gameRepository = $this->entityManager->getRepository(Game::class);
        $championshipsWithMatches = array_reduce($gameRepository->findGamesOfTheDay($date), function ($memo, Game $match) {
            if (!array_key_exists($match->getChampionship()->getName(), $memo)) {
                $memo[$match->getChampionship()->getName()]['country'] = [
                    'name' => $match->getChampionship()->getCountry()->getName(),
                    'flag' => $match->getChampionship()->getCountry()->getFlagPath(),
                ];
            }
            $memo[$match->getChampionship()->getName()]['matches'][] = $match;

            return $memo;
        }, []);

        return [
            'form' => $form->createView(),
            'championships' => $championshipsWithMatches,
        ];
    }

    /**
     * @Route("/statistics/{id}", name="statistics", methods="GET")
     * @Template(template="statistics.html.twig")
     */
    public function statistics(Request $request)
    {
        switch ($request->attributes->get('id')) {
            case '1n2':
                $type = 'winner';
                break;
            case 'under-over-2-5':
                $type = '2.5';
                break;
            case 'under-over-3-5':
                $type = '3.5';
                break;
            default :
                throw new NotFoundHttpException();
        }

        $championshipRepository = $this->entityManager->getRepository(Championship::class);
        $data = array_reduce($championshipRepository->findChampionshipsWithStatistics($type), function ($memo, $championship) {
            $teamData = [
                'name' => $championship['teamName'],
                'teamNbMatch' => $championship['teamNbMatch'],
                'percentage' => round($championship['teamPercentage'], 3),
            ];
            if (!array_key_exists($championship['name'], $memo)) {

                $memo[$championship['name']][] = [
                    'name' => $championship['name'],
                    'nbMatch' => $championship['nbMatch'],
                    'logo' => $championship['logo'],
                    'championshipPercentage' => round($championship['championshipPercentage'], 2),
                    'championshipPercentageWithForm' => round($championship['championshipPercentageWithForm'], 2),
                    'team' => $teamData,
                ];
            }

            $memo[$championship['name']]['name'] = $championship['name'];
            $memo[$championship['name']]['teams'][] = $teamData;
            $memo[$championship['name']]['nbMatch'] = $championship['nbMatch'];
            $memo[$championship['name']]['logo'] = $championship['logo'];
            $memo[$championship['name']]['championshipPercentage'] = round($championship['championshipPercentage'], 2);
            $memo[$championship['name']]['championshipPercentageWithForm'] = round($championship['championshipPercentageWithForm'], 2);

            return $memo;
        }, []);

        return [
            'data' => $data,
        ];
    }

    /**
     * @Route("/combination", name="combination", methods="GET")
     * @Template(template="combination.html.twig")
     */
    public function combination()
    {
        $payroll = [0];
        $amount = 0;
        $dates = [''];
        $combinationRepository = $this->entityManager->getRepository(Combination::class);
        /** @var Combination $combination */
        foreach ($combinationRepository->findCombinationFinished() as $combination) {
            $dates[] = $combination->getDate()->format('d/m');
            $amount = $combination->isSuccess() ? $amount + ($combination->getGeneralOdd() - Combination::BET_AMOUNT) : $amount - Combination::BET_AMOUNT;
            $payroll[] = round($amount, 2);
        }

        return [
            'combination' => $combinationRepository->findCombinationOfTheDay(new \DateTime()),
            'lastCombinations' => $combinationRepository->findLastFiveCombinations(),
            'dates' => $dates,
            'payroll' => $payroll
        ];
    }
}