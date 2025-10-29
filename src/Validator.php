<?php namespace App;

// use DateTime; // <-- CORRECCIÓN: Línea eliminada
// use Exception;  // <-- CORRECCIÓN: Línea eliminada

class Validator {

    public static function isStrongPassword(string $password): bool {
        if (strlen($password) < 8) {
            return false;
        }
        // Tu regex original
        $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/';
        return preg_match($regex, $password) === 1;
    }

    public static function isValidEmail(string $email): bool {
         return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function isNotEmpty($value): bool {
        // Asegura que no sea null y que sin espacios no esté vacío
        return $value !== null && trim((string)$value) !== '';
    }

    public static function isPositiveNumber($value): bool {
        // Usado para peso y altura
        return is_numeric($value) && floatval($value) > 0;
    }

    public static function isDateNotInFuture(string $dateString): bool {
         try {
             // Usar \DateTime para referirse a la clase global de PHP
             $inputDate = new \DateTime($dateString); // <-- CORRECCIÓN
             $today = new \DateTime(); // <-- CORRECCIÓN
             
             // Permite la fecha de hoy (comparando solo la fecha)
             return $inputDate->format('Y-m-d') <= $today->format('Y-m-d');
         } catch (\Exception $e) { // <-- CORRECCIÓN
             return false; // Formato de fecha inválido
         }
     }
}