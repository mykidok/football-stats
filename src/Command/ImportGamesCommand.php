<?php

namespace App\Command;


use App\Entity\Championship;
use App\Entity\Client;
use App\Entity\Game;
use Doctrine\DBAL\Exception\ConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ImportGamesCommand extends ContainerAwareCommand
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
        parent::__construct('api:import:games');
        $this->setDescription('Import games of the day from API Football Data');

        $this->client = $client;
        $this->em = $em;
        $this->denormalizer = $denormalizer;
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
                /** @var Game $match */
                $match = $this->denormalizer->denormalize($item, Game::class, JsonEncoder::FORMAT);

                try {
                    $this->em->persist($match);
                    $this->em->flush();
                    $i++;
                } catch (ConstraintViolationException $exception) {
                    continue;
                }
            }

            $output->writeln(sprintf('------ %d games imported for %s ------', $i, $championship->getName()));
        }
    }
}