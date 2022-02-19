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
            $data = [];
            $data['date_added'] = new \DateTime();
            
            $data['type'] = $type[$typePointer];
            $typePointer = ($typePointer + 1) & 0b1;

            $data['msrp'] = $faker->randomNumber(5);
            
            $data['year'] = $year;
            $year++;
            if($year == 2021){
                $year = 2016;
            }
            
            $data['make'] = $makes[$makeModelPointer];
            $data['model'] = $models[$makeModelPointer];
            $makeModelPointer = ($makeModelPointer + 1) & 0b11;

            $data['miles'] = $faker->randomNumber(6);

            $data['vin'] = $vinPrefix . $vin;
            $vin++;

            $data['deleted'] = false;

            $manager->persist(new Vehicle($data));
        }

        $manager->flush();
    }
}
