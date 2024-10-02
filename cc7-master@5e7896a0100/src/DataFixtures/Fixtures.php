<?php

namespace App\DataFixtures;

use App\Entity\Lesson;
use App\Entity\User;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class Fixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        $user = new User();
        $user->setNom($faker->firstName);
        $user->setPrenom($faker->lastName);
        $user->setUsername('admin');
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            'admin'
        );
        $user->setPassword($hashedPassword);
        $user->setRole('admin');
        $manager->persist($user);
        $manager->flush();

        for ($j = 0; $j < 4; $j++) {
            $entity = new Lesson();
            $entity->setName($faker->name);
            $entity->setDescription($faker->paragraph);
            $user->addLesson($entity);
            $manager->persist($entity);
        }

        $manager->flush();
        

        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setNom($faker->firstName);
            $user->setPrenom($faker->lastName);
            $user->setUsername('user' . $i);
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                'secret'
            );
            $user->setPassword($hashedPassword);
            $manager->persist($user);
            $lessons = [];
            for ($j = 0; $j < 4; $j++) {
                $entity = new Lesson();
                $entity->setName($faker->name);
                $entity->setDescription($faker->paragraph);
                array_push($lessons, $entity);
                $user->addLesson($entity);
                $manager->persist($entity);
            }

            $manager->flush();

            $user = new User();
            $user->setNom($faker->firstName);
            $user->setPrenom($faker->lastName);
            $user->setUsername('student' . $i);
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                'secret'
            );
            $user->setPassword($hashedPassword);
            $user->setRole("Etudiant");

            foreach ($lessons as $lesson) {
                $user->addStudent($lesson);
            }

            $manager->persist($user);
            $manager->flush();
        }

        $manager->flush();
    }
}
