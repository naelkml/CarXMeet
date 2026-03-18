<?php

namespace App\Form;

use App\Entity\Events;
use App\Entity\Region;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EventsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('organisateur', TextType::class, [
                'label' => 'Organisateur',
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Run' => 'Run',
                    'JDM' => 'JDM',
                    'Drift' => 'Drift',
                    'Stance' => 'Stance',
                ],
            ])
            ->add('Date', TextType::class, [
                'label' => 'Date (YYYY-MM-DD)',
            ])
            ->add('location', TextType::class, [
                'label' => 'Lieu',
            ])
            ->add('coverPhoto', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Cover',
            ])
            ->add('gallery', TextType::class, [
                'label' => 'Galerie (URLs séparées par des virgules)',
            ])
            ->add('ratingAverage', TextType::class, [
                'label' => 'Note moyenne',
                'required' => false,
            ])
            ->add('regionID', EntityType::class, [
                'label' => 'Région',
                'class' => Region::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir une région',
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Events::class,
        ]);
    }
}
