<?php



namespace App\DataFixtures;

use App\Entity\Permission;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PermissionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $entities = ['client', 'action'];
        $actions = ['list', 'view', 'edit', 'delete'];

        foreach ($entities as $entity) {
            foreach ($actions as $action) {
                $permission = new Permission();
                $permission->setEntity($entity);
                $permission->setAction($action);
                $manager->persist($permission);
            }
        }
        $permission = new Permission();
        $permission->setEntity('log');
        $permission->setAction('list');
        $manager->persist($permission);


        $manager->flush();
    }
}
