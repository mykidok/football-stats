<?php

namespace App\Form\Type;

use App\Entity\Championship;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('competition', EntityType::class, [
                'class' => Championship::class,
                'choice_label' =>'name',
                'required' => true,
                'label' => false,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                },
                'translation_domain'=> false
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => false,
                'required' => true,
                'data' => new \DateTime('today'),
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'translations.form.submit',
                'translation_domain' => 'translations',
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