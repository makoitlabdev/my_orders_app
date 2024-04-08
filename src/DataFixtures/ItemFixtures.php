<?php

namespace App\DataFixtures;

use App\Entity\Item;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ItemFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $itemsData = [
            ['name' => 'TV'],
            ['name' => 'Computer'],
            ['name' => 'Mobile'],
            ['name' => 'Tablet'],
        ];

        foreach ($itemsData as $data) {
            $item = new Item();
            $item->setName($data['name']);
            $manager->persist($item);
        }

        $manager->flush();
    }
}
