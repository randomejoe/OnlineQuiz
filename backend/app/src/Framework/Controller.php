<?php

namespace App\Framework;

class Controller
{
    public function __construct()
    {
    }

    protected function sendSuccessResponse($data = [], $code = 200)
    {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($data, JSON_PRETTY_PRINT);
    }

    protected function sendErrorResponse($message, $code = 500)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        echo json_encode(['error' => $message], JSON_PRETTY_PRINT);
    }

    /**
     * Maps POST data (JSON) to an instance of the specified class
     * 
     * @param string $className The fully qualified class name
     * @return object|null Returns an instance of the class or null if data is invalid
     */
    protected function mapPostDataToClass(string $className): ?object
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        $instance = new $className();
        
        foreach ($data as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }

        return $instance;
    }
}