<?php

namespace App\Command;

use App\Entity\Championship;
use App\Entity\Client;
use App\Entity\DataClient;
use App\Entity\Team;
use Doctrine\DBAL\Exception\ConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ImportTeamsCommand extends Command
{
    private $client;
    private $em;
    private $denormalizer;

    public function __construct(DataClient $client, EntityManagerInterface $em, DenormalizerInterface $denormalizer)
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
            $teams = $this->client->get('teams', [
                'query' => [
                    'league' =>$championship->getApiId(),
                    'season' => 2020,
                ]
            ]);

            $i = 0;

            foreach ($teams['response'] as $item) {
                $teamRepository = $this->em->getRepository(Team::class);

                /** @var Team|null $alreadyExistsTeam */
                $alreadyExistsTeam = $teamRepository->findOneBy(['apiId' => $item['team']['id']]);
                $item['championship'] = $championship;

                if (null !== $alreadyExistsTeam) {
                    if ($championship->getApiId() === $alreadyExistsTeam->getChampionship()->getApiId()) {
                        continue;
                    }

                    $alreadyExistsTeam->setChampionship($championship);
                    $this->em->persist($alreadyExistsTeam);
                    $this->em->flush();
                    $i++;

                    continue;
                }

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