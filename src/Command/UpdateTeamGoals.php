<?php

namespace App\Command;

use App\Entity\Championship;
use App\Entity\Client;
use App\Handler\TeamHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateTeamGoals extends ContainerAwareCommand
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var TeamHandler
     */
    private $teamHandler;

    public function __construct(Client $client, EntityManagerInterface $em, TeamHandler $teamHandler)
    {
        parent::__construct('api:update:teams');
        $this->setDescription('Update team away and home goals');

        $this->client = $client;
        $this->em = $em;
        $this->teamHandler = $teamHandler;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $championshipRepository = $this->em->getRepository(Championship::class);
        $championships = $championshipRepository->findAll();

        /** @var Championship $championship */
        foreach ($championships as $championship) {
            $entrypoint = sprintf('competitions/%s/standings', $championship->getApiId());
            $standings = $this->client->get($entrypoint);

            foreach ($standings['standings'] as $item) {
                if ('TOTAL' === $item['type']) {
                    continue;
                }

                $this->teamHandler->handleTeamUpdate($item, ['type' => $item['type']]);
            }
        }

        $this->em->flush();
    }
}