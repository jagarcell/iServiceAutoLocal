<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Repository\VehicleRepository;
use App\Utils\ParametersValidation;
use App\Entity\Vehicle;
use App\Utils\SqlQueryBuilder;
use App\Utils\Paginate;

class VehicleController extends AbstractController
{
    private $vehicleRepository;

    public function __construct(VehicleRepository $vehicleRepository)
    {
        $this->vehicleRepository = $vehicleRepository;
    }

    #[Route('/vehicles', name: 'vehicles')]
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/VehicleController.php',
        ]);
    }

    #[Route('/create_vehicle', name: 'createVehicle', methods: ['POST'])]
    public function create(Request $request) : JsonResponse
    {
        $result = ParametersValidation::check(
            [
                'type' => ['string'],
                'msrp' => ['double', 'integer'],
                'year' => ['integer'],
                'make' => ['string'],
                'model' => ['string'],
                'miles' => ['integer'],
                'vin' => ['string']
            ], Vehicle::class, $request
        );

        if($result['status'] == "ERROR"){
            return new JsonResponse([
                'status' => 'ERROR',
                'message' => 'MISSING AND/OR WRONG TYPE PARAMETERS!',
                'errorsLog' => $result['errorsLog']], Response:: HTTP_CREATED);
        }

        $data = $result['data'];

        $dateAdded = new \DateTime();
        $type = $data['type'];
        $msrp = $data['msrp'];
        $year = $data['year'];
        $make = $data['make'];
        $model = $data['model'];
        $miles = $data['miles'];
        $vin = $data['vin'];
        $deleted = false;

        $this->vehicleRepository->createVehicle(
            $dateAdded,
            $type,
            $msrp,
            $year,
            $make,
            $model,
            $miles,
            $vin,
            $deleted
        );

        return new JsonResponse(['status' => 'OK', 'message' => 'VEHICLE CREATED!'], Response:: HTTP_CREATED);
    }
    
    #[Route('/vehicle_filtered_sorted', name: 'vehicleFilteredSorted', methods: ['GET'])]
    public function vehiclesFilteredAndSorted(Request $request) : JsonResponse
    {
        $filtersAndSorts = \json_decode($request->getContent(), true);
        
        $vehicles = 
            $this->vehicleRepository
            ->filterAndSortVehicles(
                $filtersAndSorts,
                [
                    'type',
                    'msrp',
                    'year',
                    'make',
                    'model',
                    'miles',
                    'vin'
                ]
            );

        $data = [];

        // CHECK IF PAGINATION IS REQUIRED
        if(isset($filtersAndSorts['itemsPerPage']) && isset($filtersAndSorts['page'])){
            // AS PAGINATION IS REQUIRED LET'S DO IT
            $itemsPerPage = $filtersAndSorts['itemsPerPage'];
            $page = $filtersAndSorts['page'];

            $paginate = new Paginate($itemsPerPage, $vehicles, $this);

            $result = $paginate->fetchPage($page, "getVehicleData");

            return new JsonResponse(
                [
                    'status' => 'ok', 
                    'vehicles' => $result['data'], 
                    'itemsPerPage' => $result['itemsPerPage'], 
                    'itemsInPage' => $result['itemsInPage'],
                    'prevPage' => $result['prevPage'],
                    'page' => $result['page'],
                    'nextPage' => $result['nextPage'],
                    'total pages' => $result['totalPages']
                ], Response::HTTP_OK);
        }
        else{
            // NO PAGINATION REQUIRED
            foreach ($vehicles as $key => $vehicle) {
                # code...
                $data[] = $this->getVehicleData($vehicle);
            }
            return new JsonResponse(['status' => 'ok', 'vehicles' => $data], Response::HTTP_OK);
        }
    }

    #[Route('/vehicle/{id}', name: 'getVehicleById', methods: ['GET'])]
    public function getVehicleById($id) : JsonResponse
    {
        # code...
        $vehicle = $this->vehicleRepository->findOneBy(['id' => $id, 'deleted' => false]);
        if($vehicle === null){
            $vehicle = [];
        }
        else{
            $vehicle = $vehicle->jsonResponse();
        }

        return new JsonResponse(['status' => 'ok', 'vehicle' => $vehicle], Response::HTTP_OK);
    }

    #[Route('/vehicle/{id}', name: 'vehicleUpdate', methods: ['PATCH'])]
    public function updateVehicle($id, Request $request)
    {
        $vehicle = $this->vehicleRepository->findOneBy(['id' => $id, 'deleted' => false]);
        $data = \json_decode($request->getContent(), true);
        if($vehicle === null){
            $vehicle = [];
        }
        else{
            empty($data['date_added']) ? : $vehicle->setDateAdded($data['date_added']);
            empty($data['type']) ? : $vehicle->setType($data['type']);
            empty($data['msrp']) ? : $vehicle->setMsrp($data['msrp']);
            empty($data['year']) ? : $vehicle->setYear($data['year']);
            empty($data['make']) ? : $vehicle->setMake($data['make']);
            empty($data['model']) ? : $vehicle->setModel($data['model']);
            empty($data['miles']) ? : $vehicle->setMiles($data['miles']);
            empty($data['vin']) ? : $vehicle->setVin($data['vin']);

            $vehicle = $this->vehicleRepository->updateVehicle($vehicle)->jsonResponse();
        }

        return new JsonResponse(['status' => 'ok', 'vehicle' => $vehicle], Response::HTTP_OK);
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
}
