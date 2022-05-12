<?php
namespace App\Service;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ParametersValidation
{
    private $validator;
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }
    public function checkRequiredParameters($data, $requiredColumns)
    {
        $missingParameters = [];
        foreach ($requiredColumns as $key => $requiredColumn) {
            if(!isset($data[$requiredColumn])){
                array_push($missingParameters, $requiredColumn);
            }
        }
        return $missingParameters;
    }

    public function validate($entity){
        $errors = $this->validator->validate($entity);
        if(count($errors) > 0){
            $errorLog = (string) $errors;
            return (['status' => 'error', 'errorLog' => $errorLog]);
        }
        else{
            return(['status' => 'ok', 'entity' => $entity]);
        }
    }
}
