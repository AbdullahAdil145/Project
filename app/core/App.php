<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../controllers/MovieController.php';

class App
{
    protected $controller = null;
    protected $method = 'index';
    protected $params = [];

    public function __construct()
    {
        $action = $_GET['action'] ?? 'index';

        // Instantiate controller after requiring the file
        $this->controller = new MovieController();

        // Check if the method exists
        if (method_exists($this->controller, $action)) {
            $this->method = $action;
        } else {
            echo "404 Not Found: method '{$action}' does not exist.";
            return;
        }

        // Call the method
        call_user_func_array([$this->controller, $this->method], $this->params);
    }
}
