<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Location;
class LocationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $locations = [
            ['Name' => '99MFOR-STE', 'City' => 'saint etienne'],
            ['Name' => '99MFOR-ST2', 'City' => 'saint etienne 2'],
            ['Name' => '99MFOR-ROA', 'City' => 'Roanne'],
            ['Name' => '99MFOR-VAI', 'City' => 'Lyon'],
        ];

        foreach ($locations as $locationData) {
            $location = new Location();
            $location->setName($locationData['Name']);
            $location->setCity($locationData['City']);
            $manager->persist($location);
        }

        $manager->flush();
    }
}