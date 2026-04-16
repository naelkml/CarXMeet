<?php

namespace App\Form;

use App\Entity\Convoy;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ConvoyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('departureLocation', TextType::class, [
                'label' => 'Lieu de départ',
            ])
            ->add('departureDate', TextType::class, [
                'label' => 'Date (YYYY-MM-DD)',
                'required' => false,
            ])
            ->add('departureTime', TextType::class, [
                'label' => 'Heure (HH:MM)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Convoy::class,
        ]);
    }
}

