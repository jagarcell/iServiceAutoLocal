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

    #[Route('/vehicle', name: 'vehicle')]
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/VehicleController.php',
        ]);
    }

    #[Route('/create_vehicle', name: 'create_vehicle', methods: ['POST'])]
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

    
    #[Route('/vehicle_filtered_sorted', name: 'vehicle_filtered_sorted', methods: ['GET'])]
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
            return new JsonResponse(['status' => 'ok', 'vehicles' => $vehicles], Response::HTTP_OK);

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
