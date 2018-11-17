<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Game;
use App\Form\Type\CompetitionType;
use App\Repository\ChampionshipRepository;
use App\Repository\GameRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends Controller
{
    /**
     * @var Client $client
     */
    private $client;

    /**
     * @var FormFactoryInterface $formFactory
     */
    private $formFactory;

    /**
     * @var GameRepository
     */
    private $repository;

    /** @var ChampionshipRepository */
    private $championshipRepository;

    public function __construct(Client $client, FormFactoryInterface $formFactory, GameRepository $repository, ChampionshipRepository $championshipRepository)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->repository = $repository;
        $this->championshipRepository = $championshipRepository;
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

        $overNbGoalsMatches = [];
        $underNbGoalsMatches = [];
        $blank = true;
        $nbLimit = null;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $blank = false;
            $data = $form->getData();
            $competition = $data['competition'];
            $nbLimit = $data['nbGoals'];

            $date = $data['date'];

            $matches = $this->repository->findGamesOfTheDayForChampionship($competition, $date);

            if (empty($matches)) {
                return [
                    'nbLimit' => $nbLimit,
                    'overNbGoalsMatches' => $overNbGoalsMatches,
                    'underNbGoalsMatches' => $underNbGoalsMatches,
                    'blank' => $blank,
                    'form' => $form->createView(),
                ];
            }

            /** @var Game $match */
            foreach ($matches as $match) {
                $match->getPrevisionalNbGoals() > $nbLimit ? $overNbGoalsMatches[] = $match : $underNbGoalsMatches[] = $match;
            }
        }

        return [
            'nbLimit' => $nbLimit,
            'overNbGoalsMatches' => $overNbGoalsMatches,
            'underNbGoalsMatches' => $underNbGoalsMatches,
            'blank' => $blank,
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
            if (array_key_exists($championship['name'], $memo)) {

                $memo[$championship['name']][] = [
                    'name' => $championship['name'],
                    'nbMatch' => $championship['nbMatch'],
                    'logo' => $championship['logo'],
                    'championshipPercentage' => round($championship['championshipPercentage'], 2),
                    'team' => $teamData,
                ];
            }

            $memo[$championship['name']]['name'] = $championship['name'];
            $memo[$championship['name']]['teams'][] = $teamData;
            $memo[$championship['name']]['nbMatch'] = $championship['nbMatch'];
            $memo[$championship['name']]['logo'] = $championship['logo'];
            $memo[$championship['name']]['championshipPercentage'] = round($championship['championshipPercentage'], 2);

            return $memo;
        }, []);

        return [
            'data' => $data,
        ];
    }
}