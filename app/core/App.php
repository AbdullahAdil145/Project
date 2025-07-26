<?php

require_once __DIR__ . '/config.php'; // Load configuration file
require_once __DIR__ . '/../controllers/MovieController.php'; // Load MovieController

class App
{
    protected $controller = null; // Placeholder for the controller instance
    protected $method = 'index'; // Default method to call
    protected $params = []; // Parameters to pass to the method

    public function __construct()
    {
        $action = $_GET['action'] ?? 'index'; // Get 'action' from URL, default to 'index'

        $this->controller = new MovieController(); // Instantiate the MovieController

        if (method_exists($this->controller, $action)) {
            $this->method = $action; // Set method if it exists in the controller
        } else {
            echo "404 Not Found: method '{$action}' does not exist."; // Show error if method not found
            return;
        }

        call_user_func_array([$this->controller, $this->method], $this->params); // Call the method with parameters
    }
}
