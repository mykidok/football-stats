<?php

namespace App\Command;

use App\Entity\Championship;
use App\Entity\Client;
use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ImportGamesCommand extends Command
{
    private $client;
    private $em;
    private $denormalizer;

    public function __construct(Client $client, EntityManagerInterface $em, DenormalizerInterface $denormalizer)
    {
        parent::__construct('api:import:games');
        $this->setDescription('Import games of the day from API Football Data');

        $this->client = $client;
        $this->em = $em;
        $this->denormalizer = $denormalizer;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gameRepository = $this->em->getRepository(Game::class);
        $championshipRepository = $this->em->getRepository(Championship::class);

        /** @var Championship $championship */
        foreach ($championshipRepository->findAll() as $championship) {
            if (new \DateTime() < $championship->getStartDate()) {
                continue;
            }
            $gameDay = $this->client->get('fixtures', [
                    'query' => [
                        'league' => $championship->getApiId(),
                        'season' => (int) $championship->getStartDate()->format('Y'),
                        'date' => (new \DateTime('now'))->format('Y-m-d'),
                    ]
                ]
            );

            if (!empty($gameDay['errors'])) {
                foreach ($gameDay['errors'] as $key => $error) {
                    $output->writeln(sprintf('%s : %s', $key, $error));
                }
                sleep(6);
                continue;
            }

            if ($gameDay['results'] === 0) {
                $output->writeln(sprintf('No matches to import for %s', $championship->getName()));
                sleep(6);
                continue;
            }

            $i = 0;
            foreach ($gameDay['response'] as $item) {
                $item['championship'] = $championship;
                /** @var Game|null $match */
                $match = $this->denormalizer->denormalize($item, Game::class, JsonEncoder::FORMAT);

                if (null === $match) {
                    continue;
                }

                /** @var Game|null $matchExist */
                $matchExist = $gameRepository->findOneBy(['apiId' => $match->getApiId()]);

                if (null !== $matchExist) {
                    if (!$matchExist->isFinished()) {
                        $matchExist->setDate((new \DateTime($item['fixture']['date']))->modify('+ 1 hour'));
                        $this->em->persist($matchExist);
                        $this->em->flush();
                        $i++;
                    }
                    continue;
                }

                $this->em->persist($match);
                $this->em->flush();
                $i++;
            }

            $output->writeln(sprintf('------ %d games imported for %s ------', $i, $championship->getName()));
            sleep(6);
        }
    }
}