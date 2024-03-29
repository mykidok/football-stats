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
        switch ($id = $request->attributes->get('id')) {
            case '1n2':
                $type = 'winner';
                break;
            case 'under-over-2-5':
                $type = '2.5';
                break;
            case 'under-over-3-5':
                $type = '3.5';
                break;
            case 'both-teams-score':
                $type = 'both_teams_score';
                break;
            default :
                throw new NotFoundHttpException();
        }

        $championshipRepository = $this->entityManager->getRepository(Championship::class);
        $data = array_reduce($championshipRepository->findChampionshipsWithStatistics($type), function ($memo, $championship) {
            $teamData = [
                'name' => $championship['teamName'],
                'teamNbMatch' => $championship['teamNbMatchHome'] + $championship['teamNbMatchAway'],
                'teamNbMatchHome' => $championship['teamNbMatchHome'],
                'teamNbMatchAway' => $championship['teamNbMatchAway'],
                'percentage' => round($championship['teamPercentage'], 3),
                'homePercentage' => round($championship['teamHomePercentage'], 3),
                'awayPercentage' => round($championship['teamAwayPercentage'], 3),
            ];
            if (!array_key_exists($championship['name'], $memo)) {
                $memo[$championship['name']][] = [
                    'name' => $championship['name'],
                    'nbMatch' => $championship['nbMatch'],
                    'nbMatchWithForm' => $championship['nbMatchWithForm'],
                    'logo' => $championship['logo'],
                    'championshipPercentage' => round($championship['championshipPercentage'], 2),
                    'championshipPercentageWithForm' => round($championship['championshipPercentageWithForm'], 2),
                    'team' => $teamData,
                ];
            }

            $memo[$championship['name']]['name'] = $championship['name'];
            $memo[$championship['name']]['teams'][] = $teamData;
            $memo[$championship['name']]['nbMatch'] = $championship['nbMatch'];
            $memo[$championship['name']]['nbMatchWithForm'] = $championship['nbMatchWithForm'];
            $memo[$championship['name']]['logo'] = $championship['logo'];
            $memo[$championship['name']]['championshipPercentage'] = round($championship['championshipPercentage'], 2);
            $memo[$championship['name']]['championshipPercentageWithForm'] = round($championship['championshipPercentageWithForm'], 2);

            return $memo;
        }, []);

        return [
            'id' => $id,
            'data' => $data,
        ];
    }

    /**
     * @Route("/combination", name="combination", methods="GET")
     * @Template(template="combination.html.twig")
     */
    public function combination()
    {
        $payroll = [50];
        $amount = 50;
        $dates = [''];
        $combinationRepository = $this->entityManager->getRepository(Combination::class);
        /** @var Combination $combination */
        foreach ($combinationRepository->findCombinationFinished() as $combination) {
            $dates[] = $combination->getDate()->format('d/m');
            $amount = $combination->isSuccess() ? $amount + ($combination->getGeneralOdd() - $combination->getBet()) : $amount - $combination->getBet();
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