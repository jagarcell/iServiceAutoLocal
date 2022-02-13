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
                'message' => 'MISSING PARAMETERS!',
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
}
