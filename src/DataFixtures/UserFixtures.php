<?php

namespace App\DataFixtures;

use App\Entity\Society;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $societyRepo = $manager->getRepository(Society::class);

        $ids = $societyRepo->findOneBy(['label' => 'IDS']);
        $altra = $societyRepo->findOneBy(['label' => 'ALTRA']);

        if (!$ids || !$altra) {
            throw new \Exception('Les sociétés IDS ou ALTRA ne sont pas présentes en base.');
        }

        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setRoles(['ROLE_ADMIN,ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'admin123'));

        $user->addEntite($ids);
        $user->addEntite($altra);

        $manager->persist($user);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SocietyFixtures::class, // assure l'ordre d'exécution
        ];
    }
}
