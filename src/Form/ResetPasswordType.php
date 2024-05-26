<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire de réinitialisation du mot de passe
 */
class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('newPassword', PasswordType::class, [
                'label' => 'Nouveau mot de passe'
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => 'Confirmer le nouveau mot de passe'
            ]);
    }

/**
 * Configure les options du formulaire
 *
 * @param OptionsResolver $resolver
 * @return void
 */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            
        ]);
    }
}
