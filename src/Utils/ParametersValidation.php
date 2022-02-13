<?php
namespace App\Utils;
use App\Entity\Vehicle;

class ParametersValidation
{
    static function check($parameterNamesAndTypes, $entity, $request) {

        $errorsLog = [];
        $error = false;

        $parametersBag = json_decode($request->getContent(), true);
		foreach ($parameterNamesAndTypes as $name => $types) {	
 			if (empty($parametersBag[$name])) {
				$error = true;
                \array_push($errorsLog, [$name => 'missing']);
			}
            else{
                if(!self::validType($parametersBag[$name], $types)){
                    $error = true;
                    \array_push(
                        $errorsLog, 
                        [
                            $name => 'type missmatch ' .
                            'received "' . 
                            gettype($parametersBag[$name]) . '"' .
                            " expected " . self::acceptedTypesToString($types)
                        ]
                    );
                }
            }
 		}

        if ($error){
            return ['status' => 'ERROR', 'errorsLog' => $errorsLog];
        }
        else{
            return ['status' => 'OK', 'data' => $parametersBag];
        }
    
    }

    static function validType($var, $types)
    {
        # code...
        foreach ($types as $key => $type) {
            # code...
            if(\gettype($var) == $type){
                return true;
            }
        }
        return false;
    }

    static function acceptedTypesToString($types) : string
    {
        # code...
        $acceptedTypes = "";
        foreach ($types as $key => $type) {
            # code...
            $acceptedTypes .= ' "' . $type . '"';
        }
        return $acceptedTypes;
    }
}