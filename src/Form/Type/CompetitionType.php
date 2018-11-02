<?php

namespace App\Form\Type;


use App\Transformer\DateTimeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('competition', ChoiceType::class, [
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
            ->add('nbGoals', NumberType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Goals',
                ],
                'required' => true,
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => false,
                'attr' => [
                    'placeholder' => 'Date',
                ],
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
                'attr' => [
                    'class' => 'btn btn-primary'
                ],
            ])
        ;

        $builder->get('date')->addModelTransformer(new DateTimeTransformer());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }

}