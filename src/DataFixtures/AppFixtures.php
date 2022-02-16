<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\DBAL\Driver\IBMDB2\Exception\Factory;

use App\Entity\Vehicle;


class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $faker = \Faker\Factory::create();
        $year = 2016;

        $type = ['used', 'new'];
        $typePointer = 0b0;

        $makes = ['Toyota', 'Ford', 'Jeep', 'Dodge'];
        $makeModelPointer = 0b00;

        $models = ['Corolla', 'Escape', 'Laredo', 'Charger'];

        $vinPrefix = '1HGBH41JXMN';
        $vin = 109186;

        for($i = 0; $i < 50; $i++){
            $vehicle = new Vehicle([]);
            $vehicle->setDateAdded(new \DateTime());
            
            $vehicle->setType($type[$typePointer]);
            $typePointer = ($typePointer + 1) & 0b1;

            $vehicle->setMsrp($faker->randomNumber(5));
            
            $vehicle->setYear($year);
            $year++;
            if($year == 2021){
                $year = 2016;
            }
            
            $vehicle->setMake($makes[$makeModelPointer]);
            $vehicle->setModel($models[$makeModelPointer]);
            $makeModelPointer = ($makeModelPointer + 1) & 0b11;

            $vehicle->setMiles($faker->randomNumber(6));

            $vehicle->setVin($vinPrefix . $vin);
            $vin++;

            $vehicle->setDeleted(false);
            $manager->persist($vehicle);
        }

        $manager->flush();
    }
}
