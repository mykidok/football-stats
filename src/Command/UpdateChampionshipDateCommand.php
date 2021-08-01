<?php

namespace App\Command;

use App\Entity\Championship;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateChampionshipDateCommand extends Command
{
    private $client;
    private $em;

    public function __construct(Client $client, EntityManagerInterface $em)
    {
        parent::__construct('api:update:championships');
        $this->setDescription('Update start date of championships from API Football Data');

        $this->client = $client;
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $championshipRepository = $this->em->getRepository(Championship::class);
        $apiChampionships = $this->client->get('leagues', [
                'query' => [
                    'season' => (int) (new \DateTime())->format('Y'),
                ]
            ]
        );

        if (!empty($apiChampionships['errors'])) {
            foreach ($apiChampionships['errors'] as $key => $error) {
                $output->writeln(sprintf('%s : %s', $key, $error));
            }
        }

        if ($apiChampionships['results'] === 0) {
            $output->writeln('No championships');
        }

        foreach ($apiChampionships['response'] as $apiChampionship) {


            /** @var Championship|null $championshipExists */
            $championshipExists = $championshipRepository->findOneBy(['apiId' => $apiChampionship['league']['id']]);

            if (null === $championshipExists) {
                continue;
            }

            $championshipExists->setStartDate(new \DateTime($apiChampionship['seasons'][0]['start']));
            $this->em->persist($championshipExists);
            $this->em->flush();
            $output->writeln(sprintf('------ Start date updated for %s ------', $championshipExists->getName()));
        }
    }
}