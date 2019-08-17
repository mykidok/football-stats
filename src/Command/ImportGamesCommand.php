<?php

namespace App\Command;


use App\Entity\Championship;
use App\Entity\Client;
use App\Entity\Game;
use App\Repository\GameRepository;
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
    private $gameRepository;

    public function __construct(Client $client, EntityManagerInterface $em, DenormalizerInterface $denormalizer, GameRepository $gameRepository)
    {
        parent::__construct('api:import:games');
        $this->setDescription('Import games of the day from API Football Data');

        $this->client = $client;
        $this->em = $em;
        $this->denormalizer = $denormalizer;
        $this->gameRepository = $gameRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $championshipRepository = $this->em->getRepository(Championship::class);

        /** @var Championship $championship */
        $championships = $championshipRepository->findAll();

        foreach ($championships as $championship) {
            $gameDay = $this->client->get('matches', [
                    'query' => [
                        'competitions' => $championship->getApiId(),
                        'dateTo' => (new \DateTime('now'))->format('Y-m-d'),
                        'dateFrom' => (new \DateTime('now'))->format('Y-m-d'),
                    ]
                ]
            );

            $i = 0;
            foreach ($gameDay['matches'] as $item) {
                $item['championship'] = $championship;
                /** @var Game|null $match */
                $match = $this->denormalizer->denormalize($item, Game::class, JsonEncoder::FORMAT);

                if (null === $match) {
                    continue;
                }

                /** @var Game $matchExist */
                $matchExist = $this->gameRepository->findOneBy(['apiId' => $match->getApiId()]);

                if (null !== $matchExist) {
                    if (null === $matchExist->isGoodResult()) {
                        $matchExist->setDate((new \DateTime($item['utcDate']))->modify('+ 1 hour'));
                        $this->em->persist($matchExist);
                        $this->em->flush();
                    }
                    continue;
                }

                $this->em->persist($match);
                $this->em->flush();
                $i++;
            }

            $output->writeln(sprintf('------ %d games imported for %s ------', $i, $championship->getName()));
        }
    }
}