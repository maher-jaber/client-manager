<?php

namespace App\Form;

use App\Entity\Action;
use App\Entity\ClientActionLog;
use App\Entity\Client;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientActionLogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('performedAt')
            ->add('performedBy')
            ->add('note')
            ->add('clients', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('actions', EntityType::class, [
                'class' => Action::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClientActionLog::class,
        ]);
    }
}
