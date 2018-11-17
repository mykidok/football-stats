<?php

namespace App\Command;

use App\Entity\Championship;
use App\Entity\Client;
use App\Entity\Team;
use Doctrine\DBAL\Exception\ConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ImportTeamsCommand extends ContainerAwareCommand
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
     * @var DenormalizerInterface
     */
    private $denormalizer;

    public function __construct(Client $client, EntityManagerInterface $em, DenormalizerInterface $denormalizer)
    {
        parent::__construct('api:import:teams');
        $this
            ->setDescription('Import teams from API Football Data');

        $this->client = $client;
        $this->em = $em;
        $this->denormalizer = $denormalizer;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $championshipRepository = $this->em->getRepository(Championship::class);
        $championships = $championshipRepository->findAll();

        /** @var Championship $championship */
        foreach ($championships as $championship) {
            $entrypoint = sprintf('competitions/%d/teams', $championship->getApiId());
            $teams = $this->client->get($entrypoint);

            $i = 0;
            foreach ($teams['teams'] as $item) {
                $item['championship'] = $championship;
                $team = $this->denormalizer->denormalize($item, Team::class, JsonEncoder::FORMAT);

                try {
                    $this->em->persist($team);
                    $this->em->flush();
                    $i++;
                } catch (ConstraintViolationException $e) {
                    continue;
                }
            }

            $output->writeln(sprintf('------ %d teams imported for %s', $i, $championship->getName()));
        }
    }
}