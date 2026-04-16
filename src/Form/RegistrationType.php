<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'invalid_message' => 'Les mots de passe doivent correspondre.',
            ])
            ->add('firstName', TextType::class, ['label' => 'Prénom'])
            ->add('lastName', TextType::class, ['label' => 'Nom'])
            ->add('username', TextType::class, ['label' => 'Pseudo'])
            ->add('phone', TelType::class, ['label' => 'Téléphone'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('profilePhoto', FileType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Photo de profil',
            ])
            ->add('snapchat', TextType::class, [
                'required' => false,
                'label' => 'Snapchat',
            ])
            ->add('instagram', TextType::class, [
                'required' => false,
                'label' => 'Instagram',
            ])
            ->add('twitter', TextType::class, [
                'required' => false,
                'label' => 'X / Twitter',
            ])
            ->add('tiktok', TextType::class, [
                'required' => false,
                'label' => 'TikTok',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Créer mon compte',
                'attr' => ['class' => 'btn btn-primary w-100'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
