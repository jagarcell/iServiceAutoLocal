<?php

namespace App\Repository;

use App\Entity\Vehicle;
use App\Service\SqlQueryBuilder;
use App\Service\Paginate;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

use App\Service\ParametersValidation;



/**
 * @method Vehicle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vehicle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vehicle[]    findAll()
 * @method Vehicle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehicleRepository extends ServiceEntityRepository
{
    
    private $columnsForSearchText = [
        'type',
        'msrp',
        'year',
        'make',
        'model',
        'miles',
        'vin'
    ];

    private $requiredColumns  = [
        'type',
        'msrp',
        'year',
        'make',
        'model',
        'miles',
        'vin'
    ];

    private $manager;
    private $parametersValidation;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $manager, ParametersValidation $parametersValidation)
    {
        parent::__construct($registry, Vehicle::class);
        $this->manager = $manager;
        $this->parametersValidation = $parametersValidation;
    }

    public function createVehicle($data, $validator){

        $missingParameters = $this->parametersValidation->checkRequiredParameters($data, $this->requiredColumns);

        if(count($missingParameters) > 0){
            return ['status' => 'error', 'message' => 'MISSING PARAMETERS!', 'parameters' => $missingParameters];
        }

        $vehicle = new Vehicle($data);

        $validation = $this->parametersValidation->validate($vehicle, $validator);

        if($validation['status'] == 'ok'){
            $this->manager->persist($vehicle);
            $this->manager->flush();
            return ['status' => 'ok', 'vehicle' => $vehicle->jsonResponse(), 'message' => 'VEHICLE CREATED. I HOPE I CAN BUY ONE FOR ME SOON!'];
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

        if(isset($filtersAndSorts['filter']['date_added'])){
            $filter = $filtersAndSorts['filter']['date_added'];
            if(gettype($filter) !== "string"){
                if(isset($filter['min'])){
                    $date = $filter['min'];                    
                    $filter['min'] = strlen($date) > 10 ? $date : $date . " 00:00:00";
                }
                if(isset($filter['max'])){
                    $date = $filter['max'];                    
                    $filter['max'] = strlen($date) > 10 ? $date : $date . " 23:59:59";
                }
                if(!isset($filter['min']) && !isset($filter['max'])){
                    unset($filtersAndSorts['filter']['date_added']);
                }
            }
            else{
                $date = $filter;
                $filter = 
                [
                    'min' => substr($date, 0, 10) . " 00:00:00",
                    'max' => substr($date, 0, 10) . " 23:59:59"
                ];
                $filtersAndSorts['filter']['date_added'] = $filter;
            }
        };

        $connection = $this->getEntityManager()->getConnection();
    
        $sqlQueryBuilder = new SqlQueryBuilder('vehicle', $filtersAndSorts, $this->columnsForSearchText);

        $sql = $sqlQueryBuilder->getSelectStatement();

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
                'currentPage' => $result['page'],
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

    public function updateVehicle($id, $data, $validator)
    {
        $vehicle = $this->findOneBy(['id' => $id, 'deleted' => false]);

        if($vehicle === null){
            $vehicle = [];
        }
        else{
            !isset($data['date_added']) ? : $vehicle->setDateAdded(new \DateTime($data['date_added']));
            !isset($data['type']) ? : $vehicle->setType($data['type']);
            !isset($data['msrp']) ? $vehicle->setMsrp($vehicle->getMsrp()) : $vehicle->setMsrp($data['msrp']);
            !isset($data['year']) ? : $vehicle->setYear($data['year']);
            !isset($data['make']) ? : $vehicle->setMake($data['make']);
            !isset($data['model']) ? : $vehicle->setModel($data['model']);
            !isset($data['miles']) ? : $vehicle->setMiles($data['miles']);
            !isset($data['vin']) ? : $vehicle->setVin($data['vin']);

            $validation = $this->parametersValidation->validate($vehicle, $validator);

            if($validation['status'] == 'ok'){
                $this->manager->persist($vehicle);
                $this->manager->flush();
                $vehicle = $vehicle->jsonResponse();
            }
            else{
                return ['status' => 'error', 'message' => $validation['errorLog']];
            }
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
            'date_added' => $vehicle['date_added'],
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
