<?php
namespace App\Utils;

class ParametersValidation
{
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
