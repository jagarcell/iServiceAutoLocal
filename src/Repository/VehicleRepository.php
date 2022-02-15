<?php

namespace App\Repository;

use App\Entity\Vehicle;
use App\Utils\SqlQueryBuilder;
use App\Utils\Paginate;

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

    public function createVehicle($data, $validator){

        $vehicle = new Vehicle();

        $vehicle->setDateAdded(new \DateTime());
        $vehicle->setType(empty($data['type']) ? -1 : $data['type']);
        $vehicle->setMsrp(empty($data['msrp']) ? "" : $data['msrp']);
        $vehicle->setYear(empty($data['year']) ? "" : $data['year']);
        $vehicle->setMake(empty($data['make']) ? -1 : $data['make']);
        $vehicle->setModel(empty($data['model']) ? -1 : $data['model']);
        $vehicle->setMiles(empty($data['miles']) ? "" : $data['miles']);
        $vehicle->setVin(empty($data['vin']) ? -1 : $data['vin']);
        $vehicle->setDeleted(false);

        $validation = (new ParametersValidation())->validate($vehicle, $validator);

        if($validation['status'] == 'ok'){
            $this->manager->persist($vehicle);
            $this->manager->flush();
            return ['status' => 'ok', 'message' => 'VEHICLE CREATED!'];
        }
        else{
            return ['status' => 'error', 'message' => $validation['errorLog']];
        }
    }

    public function filterAndSortVehicles($filtersAndSorts)
    {

        if(!isset($filtersAndSorts['filter']) || !isset($filtersAndSorts['deleted'])){
            $filtersAndSorts['filter']['deleted'] = false;
        }

        $columnNames = [
            'type',
            'msrp',
            'year',
            'make',
            'model',
            'miles',
            'vin'
        ];

        $connection = $this->getEntityManager()->getConnection();
    
        $sqlQueryBuilder = new SqlQueryBuilder('vehicle', $filtersAndSorts, $columnNames);

        $sql = $sqlQueryBuilder->buildSelectString();

        $statement = $connection->prepare($sql);
        $resultSet = $statement->executeQuery();

        // returns an array of arrays (i.e. a raw data set)
        $vehicles = $resultSet->fetchAllAssociative();

        $data = [];

        // CHECK IF PAGINATION IS REQUIRED
        if(isset($filtersAndSorts['itemsPerPage']) && isset($filtersAndSorts['page'])){
            // AS PAGINATION IS REQUIRED LET'S DO IT
            $itemsPerPage = $filtersAndSorts['itemsPerPage'];
            $page = $filtersAndSorts['page'];

            $paginate = new Paginate($itemsPerPage, $vehicles, $this);

            $result = $paginate->fetchPage($page, "getVehicleData");

            return [
                'status' => 'ok', 
                'vehicles' => $result['data'], 
                'itemsPerPage' => $result['itemsPerPage'], 
                'itemsInPage' => $result['itemsInPage'],
                'prevPage' => $result['prevPage'],
                'page' => $result['page'],
                'nextPage' => $result['nextPage'],
                'total pages' => $result['totalPages']
            ];
        }
        else{
            // NO PAGINATION REQUIRED
            foreach ($vehicles as $key => $vehicle) {
                # code...
                $data[] = $this->getVehicleData($vehicle);
            }
            return ['status' => 'ok', 'vehicles' => $data];
        }

    }

    public function updateVehicle($id, $data) : Vehicle
    {
        $vehicle = $this->vehicleRepository->findOneBy(['id' => $id, 'deleted' => false]);
        if($vehicle === null){
            $vehicle = [];
        }
        else{
            $data = \json_decode($request->getContent(), true);

            empty($data['date_added']) ? : $vehicle->setDateAdded($data['date_added']);
            empty($data['type']) ? : $vehicle->setType($data['type']);
            empty($data['msrp']) ? : $vehicle->setMsrp($data['msrp']);
            empty($data['year']) ? : $vehicle->setYear($data['year']);
            empty($data['make']) ? : $vehicle->setMake($data['make']);
            empty($data['model']) ? : $vehicle->setModel($data['model']);
            empty($data['miles']) ? : $vehicle->setMiles($data['miles']);
            empty($data['vin']) ? : $vehicle->setVin($data['vin']);

            $this->manager->persist($vehicle);
            $this->manager->flush();
            $vehicle = $vehicle->jsonResponse();
        }
        return ['status' => 'ok', 'vehicle' => $vehicle];
    }

    public function getVehicleById($id)
    {
        $vehicle = $this->findOneBy($id);
        if($vehicle === null){
            $vehicle = [];
        }
        else{
            $vehicle = $vehicle->jsonResponse();
        }
        return ['status' => 'ok', 'vehicle' => $vehicle];
    }

    public function deleteVehicle($id)
    {
        $vehicle = $this->findOneBy(['id' => $id, 'deleted' => false]);
        if($vehicle === null){
            $vehicle = [];
        }
        else{
            $vehicle->setDeleted(true);

            $this->manager->persist($vehicle);
            $this->manager->flush();
    
            $vehicle = $vehicle->jsonResponse();
        }
        return ['status' => 'OK', 'vehicle' => $vehicle];
    }

    public function getVehicleData($vehicle)
    {
        return [
            'id' => $vehicle['id'],
            'dateAdded' => $vehicle['date_added'],
            'type' => $vehicle['type'],
            'msrp' => $vehicle['msrp'],
            'year' => $vehicle['year'],
            'make' => $vehicle['make'],
            'model' => $vehicle['model'],
            'miles' => $vehicle['miles'],
            'vin' => $vehicle['vin']
        ];
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
