<?php
namespace App\Service;

class ParametersValidation
{
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

    public function validate($entity, $validator){
        $errors = $validator->validate($entity);
        if(count($errors) > 0){
            $errorLog = (string) $errors;
            return (['status' => 'error', 'errorLog' => $errorLog]);
        }
        else{
            return(['status' => 'ok', 'entity' => $entity]);
        }
    }
}
