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

        $result = $this->vehicleRepository->createVehicle($data, $validator);

        return new JsonResponse($result, Response:: HTTP_CREATED);

    }
    
    #[Route('/vehicle_filtered_sorted', name: 'vehicleFilteredSorted', methods: ['GET'])]
    public function vehiclesFilteredAndSorted(Request $request) : JsonResponse
    {
        $filtersAndSorts = \json_decode($request->getContent(), true);
        $filtersAndSorts['filter']['type'] = $this->getParameter('app.vehicleType');
        $result = $this->vehicleRepository->filterAndSortVehicles($filtersAndSorts);

        return new JsonResponse($result, Response::HTTP_OK);
    }

    #[Route('/vehicle/{id}', name: 'getVehicleById', methods: ['GET'])]
    public function getVehicleById($id) : JsonResponse
    {
        # code...
        $result = $this->vehicleRepository->getVehicleById(['id' => $id, 'type' => $this->getParameter('app.vehicleType'), 'deleted' => false]);

        return new JsonResponse($result, Response::HTTP_OK);
    }

    #[Route('/vehicle/{id}', name: 'vehicleUpdate', methods: ['PATCH'])]
    public function updateVehicle($id, Request $request)
    {
        $data = \json_decode($request->getContent(), true);
        $result = $this->vehicleRepository->updateVehicle($id, $data);

        return new JsonResponse($result, Response::HTTP_OK);
    }

    #[Route('/vehicle/{id}', name: 'deleteVehicle', methods: ['DELETE'])]
    public function deleteVehicle($id) : JsonResponse
    {
        $result = $this->vehicleRepository->deleteVehicle($id);

        return new JsonResponse($result, Response::HTTP_OK);
    }
}
