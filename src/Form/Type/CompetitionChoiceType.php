<?php

namespace App\Form\Type;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetitionChoiceType extends AbstractType
{
    /** @var Client $client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'key' => 'id',
            'choices' => function(Options $options) {
                $collection = $this->client->get('competitions');

                $choices = [];
                foreach ($collection['competitions'] as $competition) {
                    $value = $competition['name'];
                    $choices[$value] = $competition[$options['key']];
                }
                return $choices;
            }
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

}