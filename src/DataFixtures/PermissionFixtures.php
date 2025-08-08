<?php



namespace App\DataFixtures;

use App\Entity\Permission;
use App\Entity\Society;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PermissionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $societyRepo = $manager->getRepository(Society::class);

        $ids = $societyRepo->findOneBy(['label' => 'IDS']);
        $altra = $societyRepo->findOneBy(['label' => 'ALTRA']);

        if (!$ids || !$altra) {
            throw new \Exception('Les sociétés IDS ou ALTRA ne sont pas présentes en base.');
        }

        $entities = ['client', 'action'];
        $actions = ['list', 'view', 'edit', 'delete'];

        foreach ($entities as $entity) {
            foreach ($actions as $action) {
                $permission = new Permission();
                $permission->setEntity($entity);
                $permission->setAction($action);
                $permission->setEntreprise($ids);
          
                $manager->persist($permission);
            }
        }
        $permission = new Permission();
        $permission->setEntity('log');
        $permission->setAction('list');
        $permission->setEntreprise($ids);
       
        $manager->persist($permission);


        // for altra 

        foreach ($entities as $entity) {
            foreach ($actions as $action) {
                $permission = new Permission();
                $permission->setEntity($entity);
                $permission->setAction($action);
              
                $permission->setEntreprise($altra);
                $manager->persist($permission);
            }
        }
        $permission = new Permission();
        $permission->setEntity('log');
        $permission->setAction('list');
        
        $permission->setEntreprise($altra);
        $manager->persist($permission);


        $manager->flush();
    }
}
