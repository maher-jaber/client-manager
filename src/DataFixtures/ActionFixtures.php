<?php

namespace App\DataFixtures;

use App\Entity\Action;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ActionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $labels = [
            'Analyse Antivirus',
            'Sauvegarde Altra backup',
            'Sauvegarde Cobian',
            'Vérification pc',
        ];

        foreach ($labels as $label) {
            $action = new Action();
            $action->setLabel($label);
            $manager->persist($action);
        }

        $manager->flush();
    }
}
