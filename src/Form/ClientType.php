<?php

namespace App\Form;

use App\Entity\Action;
use App\Entity\Client;
use App\Repository\ActionRepository;
use App\Repository\SocietyRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entreprise = $options['entreprise'] ?? null;

        $builder
            ->add('commercial', TextType::class, ['required' => false])
            ->add('nomClient', TextType::class)
            ->add('dateProposition', DateType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('numeroContrat', TextType::class, ['required' => false])
            ->add('refuse', CheckboxType::class, ['required' => false])
            ->add('accordSigne', CheckboxType::class, ['required' => false])
            ->add('demarrageContrat', DateType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('premiereFacturation', DateType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('numeroClient', TextType::class, ['required' => false])
            ->add('telephoneCabinet', TextType::class, ['required' => false])
            ->add('gsmPraticien', TextType::class, ['required' => false])
            ->add('email', EmailType::class, ['required' => false])
            ->add('actions', EntityType::class, [
                'class' => Action::class,
                'choice_label' => 'label',
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                'query_builder' => function (ActionRepository $er) use ($options) {
                    $entreprise = $options['entreprise'];
                    return $er->createQueryBuilder('a')
                        ->where('a.entite = :entite')
                        ->setParameter('entite', $entreprise)
                        ->orderBy('a.label', 'ASC');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
        // ✅ On ajoute ici l’option personnalisée
        $resolver->setDefined('entreprise');
    }
}
