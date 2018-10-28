<?php

namespace App\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('competition', CompetitionChoiceType::class, [
                'choices' => [
                    'Bundesliga' => '2002',
                    'Championship' => '2016',
                    'Eredivisie' => '2003',
                    'Liga' => '2014',
                    'Ligue 1' => '2015',
                    'Serie A' => '2019',
                    'Primeira Liga' => '2017',
                    'Premier League' => '2021',
                ],
                'required' => true,
                'label' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
                'attr' => [
                    'class' => 'btn btn-primary'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }

}