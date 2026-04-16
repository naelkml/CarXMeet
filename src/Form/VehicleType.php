<?php

namespace App\Form;

use App\Entity\Vehicle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class VehicleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('brand', TextType::class, [
                'label' => 'Marque',
            ])
            ->add('model', TextType::class, [
                'label' => 'Modèle',
            ])
            ->add('year', TextType::class, [
                'label' => 'Année',
            ])
            ->add('engine', TextType::class, [
                'label' => 'Moteur',
            ])
            ->add('preparation', TextType::class, [
                'label' => 'Préparation',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('coverPhoto', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Photo de couverture',
            ]);

        $builder->add('galleryPhotos', FileType::class, [
            'mapped' => false,
            'required' => false,
            'multiple' => true,
            'label' => 'Galerie (max 5 photos)',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicle::class,
        ]);
    }
}
