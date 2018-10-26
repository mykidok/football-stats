<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\Type\CompetitionChoiceType;
use App\Form\Type\CompetitionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
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
    public function indexAction()
    {
        $form = $this->formFactory
            ->createNamed(
                '',
                CompetitionType::class,
                [],
                []
            );

        return [
            'form' => $form->createView(),
        ];
    }


}