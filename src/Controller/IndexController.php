<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\Type\CompetitionChoiceType;
use App\Form\Type\CompetitionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends Controller
{
    /** @var  Client $client */
    private $client;

    /** @var FormFactoryInterface $formFactory */
    private $formFactory;

    public function __construct(Client $client, FormFactoryInterface $formFactory)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
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

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $competitionId = $data['competition'];

            $today = (new \DateTime())->format('Y-m-d');
            $dayMatches = $this->client->get('matches', [
                'query' => [
                    'competitions' => $competitionId,
                    'dateTo' => $today,
                    'dateFrom' => $today,
                ]]
            );
            unset($dayMatches['count'], $dayMatches['filters']);

            if (empty($dayMatches)) {
                return [
                    'overNbGoalsMatches' => $overNbGoalsMatches,
                    'underNbGoalsMatches' => $underNbGoalsMatches,
                    'form' => $form->createView(),
                ];
            }

            $standings = $this->client->get(sprintf('competitions/%s/standings', $competitionId));

            foreach ($standings['standings'] as $standing) {
                if ('TOTAL' === $standing['type']) {
                    continue;
                }

                foreach ($standing['table'] as $table) {
                    if ('HOME' === $standing['type']) {
                        foreach ($dayMatches['matches'] as &$dayMatch) {
                            if ($dayMatch['homeTeam']['id'] === $table['team']['id']) {
                                $nbGoals = $table['goalsFor'] + $table['goalsAgainst'];
                                $playedGames = $table['playedGames'];
                                $dayMatch['homeTeam']['nbGoalsPerMatch'] = $nbGoals/$playedGames;
                            }
                        }
                    }
                    if ('AWAY' === $standing['type']) {
                        foreach ($dayMatches['matches'] as &$dayMatch) {
                            if ($dayMatch['awayTeam']['id'] === $table['team']['id']) {
                                $nbGoals = $table['goalsFor'] + $table['goalsAgainst'];
                                $playedGames = $table['playedGames'];
                                $dayMatch['awayTeam']['nbGoalsPerMatch'] = $nbGoals/$playedGames;
                            }
                        }
                    }

                }
            }

            foreach ($dayMatches['matches'] as &$dayMatch) {
                $previsionalNbGoals = ($dayMatch['homeTeam']['nbGoalsPerMatch'] + $dayMatch['awayTeam']['nbGoalsPerMatch']) / 2;

                $payload = [
                    'homeTeam' => $dayMatch['homeTeam'],
                    'awayTeam' => $dayMatch['awayTeam'],
                    'previsionalNbGoals' => $previsionalNbGoals,
                ];

                if ($previsionalNbGoals > 2.5) {
                    $overNbGoalsMatches[] = $payload;
                } else {
                    $underNbGoalsMatches[] = $payload;
                }
            }
        }

        return [
            'overNbGoalsMatches' => $overNbGoalsMatches,
            'underNbGoalsMatches' => $underNbGoalsMatches,
            'form' => $form->createView(),
        ];
    }


}