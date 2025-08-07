<?php

namespace App\DataFixtures;

use App\Entity\Action;
use App\Entity\Society;
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
            'Sauvegarde Veeam',
            'Antivirus Bitdefender',
            'Altra Backup',
            'Espace serveur'
        ];

        foreach ($labels as $label) {
            $action = new Action();
            $action->setLabel($label);
            $action->setEntite($manager->getRepository(Society::class)->findOneBy(['label'=>'IDS']));
            
            $manager->persist($action);
        }

        $manager->flush();
    }
}
