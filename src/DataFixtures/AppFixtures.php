<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $passwordHash = password_hash('s3cr3t', PASSWORD_BCRYPT);

        $user = new User()
            ->withEmail('alef@example.com')
            ->withPasswordHash($passwordHash);

        $manager->persist($user);
        $manager->flush();
    }
}
