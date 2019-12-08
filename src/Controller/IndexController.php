<?php

namespace App\Controller;

use App\Entity\Championship;
use App\Entity\Client;
use App\Entity\Combination;
use App\Entity\Game;
use App\Form\Type\CompetitionType;
use App\Repository\ChampionshipRepository;
use App\Repository\CombinationRepository;
use App\Repository\GameRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends Controller
{
    private $client;
    private $formFactory;
    private $gameRepository;
    private $championshipRepository;
    private $combinationRepository;

    public function __construct(Client $client, FormFactoryInterface $formFactory, GameRepository $gameRepository, ChampionshipRepository $championshipRepository, CombinationRepository $combinationRepository)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->gameRepository = $gameRepository;
        $this->championshipRepository = $championshipRepository;
        $this->combinationRepository = $combinationRepository;
    }

    /**
     * @Route(
     *     path="",
     *     name="home"
     * )
     */
    public function homeAction()
    {
        return $this->render('home.html.twig');
    }

    /**
     * @Route(
     *     path="/bets",
     *     name="bets",
     *     methods={"GET|POST"}
     * )
     *
     * @Template(template="bets.html.twig")
     */
    public function betsAction(Request $request)
    {
        $form = $this->formFactory
            ->createNamed(
                '',
                CompetitionType::class,
                [],
                []
            );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $competition = $data['competition'];
            $date = $data['date'];
        } else {
            /** @var Championship $competition */
            $competition = $this->championshipRepository->findOneBy(['name' => 'Bundesliga']);
            $date = new \DateTime('today');
        }

        $matches = $this->gameRepository->findGamesOfTheDayForChampionship($competition, $date);

        $overNbGoalsMatches = [];
        $underNbGoalsMatches = [];
        $nbLimit = Game::LIMIT;

        /** @var Game $match */
        foreach ($matches as $match) {
            $match->getAverageExpectedNbGoals() > $nbLimit ? $overNbGoalsMatches[] = $match : $underNbGoalsMatches[] = $match;
        }

        return [
            'nbLimit' => $nbLimit,
            'matches' => $matches,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route(
     *     path="/statistics",
     *     name="statistics",
     *     methods={"GET"}
     * )
     *
     * @Template(template="statistics.html.twig")
     */
    public function statisticsAction()
    {
        $championships = $this->championshipRepository->findChampionshipsWithStatistics();

        $data = array_reduce($championships, function ($memo, $championship) {
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
     * @Route(
     *     path="/combination",
     *     name="combination",
     *     methods={"GET"}
     * )
     *
     * @Template(template="combination.html.twig")
     */
    public function combinationAction()
    {
        $combinationDay = $this->combinationRepository->findCombinationOfTheDay(new \DateTime());
        $lastCombinations = $this->combinationRepository->findLastFiveCombinations();

        $combinations = $this->combinationRepository->findCombinationFinished();

        $payroll = [0];
        $amout = 0;
        $dates = [''];
        /** @var Combination $combination */
        foreach ($combinations as $combination) {
            $dates[] = $combination->getDate()->format('d/m');

            if ($combination->isSuccess()) {
                $amout = $amout + ($combination->getGeneralOdd() - Combination::BET_AMOUNT);
            } else {
                $amout = $amout - Combination::BET_AMOUNT;
            }
            $payroll[] = round($amout, 2);
        }

        return [
            'combination' => $combinationDay,
            'lastCombinations' => $lastCombinations,
            'dates' => $dates,
            'payroll' => $payroll
        ];
    }
}