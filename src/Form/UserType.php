<?php

namespace App\Form;

use App\Entity\Permission;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'expanded' => true,
                'multiple' => true,
                'by_reference' => false,
                'label' => 'Rôles',
            ])

            ->add('password', PasswordType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Mot de passe (laisser vide pour ne pas changer)',
            ])
            ->add('permissions', EntityType::class, [
                'class' => Permission::class,
                'choice_label' => function (Permission $p) {
                    return $p->getEntreprise()?->getLabel() . ' => ' . $p->getEntity() . ' : ' . ucfirst($p->getAction());
                },
                'multiple' => true,
                'expanded' => true,
                'label' => 'Permissions spécifiques',
                'by_reference' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
