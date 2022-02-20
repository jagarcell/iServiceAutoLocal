<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Repository\VehicleRepository;
use App\Service\ParametersValidation;
use App\Entity\Vehicle;

class VehicleUpdates{

    private $requiredColumns  = [
        'type',
        'msrp',
        'year',
        'make',
        'model',
        'miles',
        'vin'
    ];

    private $vehicleRepository;
    private $entityManager;
    private $validator;
    private $parametersValidation;
    public function __construct(
        VehicleRepository $vehicleRepository,
        EntityManagerInterface $entityManager, 
        ValidatorInterface $validator, 
        ParametersValidation $parametersValidation)
    {
        $this->vehicleRepository = $vehicleRepository;
        $this->manager = $entityManager;
        $this->validator = $validator;
        $this->parametersValidation = $parametersValidation;
    }

    public function createVehicle($data){
        $missingParameters = $this->parametersValidation->checkRequiredParameters($data, $this->requiredColumns);

        if(count($missingParameters) > 0){
            return ['status' => 'error', 'message' => 'MISSING PARAMETERS!', 'parameters' => $missingParameters];
        }

        $vehicle = new Vehicle($data);

        $validation = $this->parametersValidation->validate($vehicle);

        if($validation['status'] == 'ok'){
            $this->manager->persist($vehicle);
            $this->manager->flush();
            return ['status' => 'ok', 'vehicle' => $vehicle->jsonResponse(), 'message' => 'VEHICLE CREATED. I HOPE I CAN BUY ONE FOR ME SOON!'];
        }
        else{
            return ['status' => 'error', 'message' => $validation['errorLog']];
        }
    }

    public function updateVehicle($id, $data)
    {
        $vehicle = $this->vehicleRepository->findOneBy(['id' => $id, 'deleted' => false]);

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

            $validation = $this->parametersValidation->validate($vehicle);

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

}