<?php
class Controller {
    // Load a view file and pass data to it
    public function view($view, $data = []) {
        extract($data); // Convert array keys into variables
        require_once "app/views/$view.php"; // Include the corresponding view file
    }
}
