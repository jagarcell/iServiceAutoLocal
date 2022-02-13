<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Repository\VehicleRepository;

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
        $data = json_decode($request->getContent(), true);

        if(
            empty($data['type']) ||
            empty($data['msrp']) ||
            empty($data['year']) ||
            empty($data['make']) ||
            empty($data['model']) ||
            empty($data['miles']) ||
            empty($data['vin'])
        ){
            return new JsonResponse(['status' => 'MISSING REQUIRED PARAMETERS!'], Response:: HTTP_CREATED);
        }

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

        return new JsonResponse(['status' => 'VEHICLE CREATED!'], Response:: HTTP_CREATED);
    }
}
