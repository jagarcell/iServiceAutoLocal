<?php

namespace App\Repository;

use App\Entity\Vehicle;
use App\Utils\SqlQueryBuilder;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;


/**
 * @method Vehicle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vehicle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vehicle[]    findAll()
 * @method Vehicle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehicleRepository extends ServiceEntityRepository
{
    private $manager;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $manager)
    {
        parent::__construct($registry, Vehicle::class);
        $this->manager = $manager;
    }

    public function createVehicle(
        $dateAdded,
        $type,
        $msrp,
        $year,
        $make,
        $model,
        $miles,
        $vin,
        $deleted
    ){
        $newVehicle = new Vehicle();

        $newVehicle->setDateAdded($dateAdded);
        $newVehicle->setType($type);
        $newVehicle->setMsrp($msrp);
        $newVehicle->setYear($year);
        $newVehicle->setMake($make);
        $newVehicle->setModel($model);
        $newVehicle->setMiles($miles);
        $newVehicle->setVin($vin);
        $newVehicle->setDeleted($deleted);

        $this->manager->persist($newVehicle);
        $this->manager->flush();
    }

    public function filterAndSortVehicles($criteria, $columnNames = [])
    {
        if(!isset($criteria['filter']) || !isset($criteria['deleted'])){
            $criteria['filter']['deleted'] = false;
        }

        $connection = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT * FROM vehicle v';

        $sql .= SqlQueryBuilder::filterSql($criteria);
        $sql .= SqlQueryBuilder::searchSql($criteria, $columnNames);
        $sql .= SqlQueryBuilder::sortSql($criteria);

        $statement = $connection->prepare($sql);
        $resultSet = $statement->executeQuery();

        // returns an array of arrays (i.e. a raw data set)
        return $resultSet->fetchAllAssociative();
    }

    // /**
    //  * @return Vehicle[] Returns an array of Vehicle objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Vehicle
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
