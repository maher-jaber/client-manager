<?php
namespace App\DataFixtures;

use App\Entity\Society;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

class SocietyFixtures extends Fixture implements OrderedFixtureInterface
{
    

    public function load(ObjectManager $manager): void
    {
        $altra = new Society();
        $altra->setLabel('ALTRA');
        $manager->persist($altra);
        

        $ids = new Society();
        $ids->setLabel('IDS');
        $manager->persist($ids);
        

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 1; // s'exécute avant les autres fixtures qui dépendent de Society
    }
}
