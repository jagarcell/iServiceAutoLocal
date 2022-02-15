<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    public function create(Request $request, ValidatorInterface $validator) : JsonResponse
    {
        $data = \json_decode($request->getContent(), true);
        
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
            $this->vehicleRepository->createVehicle($vehicle);
            return new JsonResponse(['status' => 'ok', 'message' => 'VEHICLE CREATED!'], Response:: HTTP_CREATED);
        }
        else{
            return new JsonResponse(['status' => 'error', 'message' => $validation['errorLog']], Response:: HTTP_CREATED);
        }
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

            $vehicle = $this->vehicleRepository->updateVehicle($vehicle)->jsonResponse();
        }

        return new JsonResponse(['status' => 'ok', 'vehicle' => $vehicle], Response::HTTP_OK);
    }

    #[Route('/vehicle/{id}', name: 'deleteVehicle', methods: ['DELETE'])]
    public function deleteVehicle($id) : JsonResponse
    {
        $vehicle = $this->vehicleRepository->findOneBy(['id' => $id, 'deleted' => false]);
        if($vehicle === null){
            $vehicle = [];
        }
        else{
            $vehicle->setDeleted(true);
            $vehicle = $this->vehicleRepository->deleteVehicle($vehicle)->jsonResponse();
        }

        return new JsonResponse(['status' => 'OK', 'vehicle' => $vehicle], Response::HTTP_OK);
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
