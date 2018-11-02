<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Match;
use App\Entity\Standing;
use App\Entity\Table;
use App\Entity\Team;
use App\Form\Type\CompetitionType;
use App\Handler\MatchDayHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends Controller
{
    /** @var  Client $client */
    private $client;

    /** @var FormFactoryInterface $formFactory */
    private $formFactory;

    /** @var MatchDayHandler */
    private $handler;

    public function __construct(Client $client, FormFactoryInterface $formFactory, MatchDayHandler $handler)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->handler = $handler;
    }

    /**
     * @Route(
     *     path="/index",
     *     name="index",
     *     methods={"GET|POST"}
     * )
     *
     * @Template(template="home.html.twig")
     */
    public function indexAction(Request $request)
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
            $competitionId = $data['competition'];
            $nbLimit = $data['nbGoals'];

            $date = $data['date'];

            $dayMatches = $this->client->getMatchDay([
                'query' => [
                    'competitions' => $competitionId,
                    'dateTo' => $date,
                    'dateFrom' => $date,
                ]]
            );

            if ($dayMatches->getMatches()->isEmpty()) {
                return [
                    'overNbGoalsMatches' => $overNbGoalsMatches,
                    'underNbGoalsMatches' => $underNbGoalsMatches,
                    'blank' => $blank,
                    'form' => $form->createView(),
                ];
            }

            $entrypoint = sprintf('competitions/%s/standings', $competitionId);
            $globalStanding = $this->client->getGlobalStanding($entrypoint);

            /** @var Match $match */
            foreach ($dayMatches->getMatches() as $match) {
                $this->handler->handleTeam($match->getHomeTeam(), $globalStanding->getHomeStanding());
                $this->handler->handleTeam($match->getAwayTeam(), $globalStanding->getAwayStanding());

                $previsionalNbGoals = ($match->getHomeTeam()->getNbGoalsPerMatch() + $match->getAwayTeam()->getNbGoalsPerMatch()) / 2;
                $match->setPrevisionalNbGoals(round($previsionalNbGoals, 3));
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

    private function matchIsComplete(Match $match) {
        if (null !== $match->getHomeTeam()->getNbGoalsPerMatch()
                && null !== $match->getAwayTeam()->getNbGoalsPerMatch()) {
            return true;
        }

        return false;
    }
}