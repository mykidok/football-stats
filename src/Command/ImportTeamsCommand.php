<?php

namespace App\Command;

use App\Entity\Championship;
use App\Entity\Client;
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
        $teamRepository = $this->em->getRepository(Team::class);
        $championships = $championshipRepository->findAll();

        $teamsImported = [];
        /** @var Championship $championship */
        foreach ($championships as $championship) {
            $teams = $this->client->get('teams', [
                'query' => [
                    'league' => $championship->getApiId(),
                    'season' => (int) $championship->getStartDate()->format('Y'),
                ]
            ]);

            $i = 0;

            foreach ($teams['response'] as $item) {
                $teamRepository = $this->em->getRepository(Team::class);
                $teamsImported[$championship->getId()][] = $item['team']['id'];
                /** @var Team|null $alreadyExistsTeam */
                $alreadyExistsTeam = $teamRepository->findOneBy(['apiId' => $item['team']['id']]);
                $item['championship'] = $championship;

                if (null !== $alreadyExistsTeam) {
                    if (null !== $alreadyExistsTeam->getChampionship() && $championship->getApiId() === $alreadyExistsTeam->getChampionship()->getApiId()) {
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

            sleep(6);
            $output->writeln(sprintf('------ %d teams imported for %s', $i, $championship->getName()));
        }

        /** @var Championship $championship */
        foreach ($championships as $championship) {
            /** @var Team $team */
            foreach ($teamRepository->findBy(['championship' => $championship->getId()]) as $team) {
                if (!in_array($team->getApiId(), $teamsImported[$championship->getId()])) {
                    $team->setChampionship(null);
                    $this->em->persist($team);
                    $output->writeln(sprintf('------ %s removed from %s', $team->getName(), $championship->getName()));
                }
            }
        }
        $this->em->flush();
    }
}