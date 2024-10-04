<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixture extends Fixture
{

    public function load(ObjectManager $manager): void
{
    $types = [
        'itinéraires pédagogiques',
        'Supports',
        'test de fin de formation',
    ];

    foreach ($types as $typeName) {
        $documentType = new Category();
        $documentType->setName($typeName);
        $manager->persist($documentType);
    }

    $manager->flush();
}
}
