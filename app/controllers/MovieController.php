<?php
require_once 'app/core/Controller.php'; // Include base controller
require_once 'app/models/Movie.php'; // Include the Movie model

class MovieController extends Controller
{
    // Handles displaying top movies optionally filtered by rating
    public function index()
    {
        $movieModel = new Movie(); // Create an instance of the Movie model
        $rating = $_GET['rating'] ?? null; // Get rating filter from URL if provided

        if ($rating) {
            $topMovies = $movieModel->getMoviesByRating($rating); // Get movies filtered by rating
        } else {
            $topMovies = $movieModel->getTopRated(); // Get top-rated movies if no filter
        }

        $this->view('search', ['topMovies' => $topMovies, 'selectedRating' => $rating]); // Render search view
    }

    // Handles searching movies by title and optional year
    public function search()
    {
        $title = $_GET['title'] ?? ''; // Get title from URL
        $year = $_GET['year'] ?? null; // Get year from URL if provided

        if ($title) {
            $movieModel = new Movie(); // Create an instance of the Movie model
            $movies = $movieModel->searchMoviesByTitle($title, $year); // Search movies by title and year
            $this->view('result', ['movies' => $movies, 'query' => $title, 'year' => $year]); // Render result view
        } else {
            header("Location: index.php"); // Redirect if no title provided
        }
    }

    // Displays detailed information and AI reviews for a specific movie
    public function details()
    {
        $title = $_GET['title'] ?? ''; // Get movie title from URL
        $year = $_GET['year'] ?? null; // Get movie year from URL if provided

        if ($title) {
            $movieModel = new Movie(); // Create an instance of the Movie model
            $movie = $movieModel->getMovieByTitle($title); // Fetch movie details
            $aiReviews = $movieModel->generateAIReviews($title, $movie['Year'] ?? $year); // Generate AI reviews

            $userSession = session_id(); // Get current user session ID
            $userRating = $movieModel->getRating($title, $movie['Year'] ?? $year, $userSession); // Get user's rating
            $avgRating = $movieModel->getAverageRating($title, $movie['Year'] ?? $year); // Get average rating and count

            // Render details view with all data
            $this->view('details', [
                'movie' => $movie, 
                'aiReviews' => $aiReviews,
                'userRating' => $userRating,
                'avgRating' => $avgRating
            ]);
        } else {
            header("Location: index.php"); // Redirect if no title is provided
        }
    }

    // Handles saving a user's movie rating via POST
    public function rate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? ''; // Get movie title from POST
            $year = $_POST['year'] ?? ''; // Get movie year from POST
            $rating = $_POST['rating'] ?? 0; // Get rating value from POST

            if ($title && $year && $rating >= 1 && $rating <= 5) {
                $movieModel = new Movie(); // Create an instance of the Movie model
                $userSession = session_id(); // Get current user session ID

                $success = $movieModel->saveRating($title, $year, $userSession, $rating); // Save the rating

                header('Content-Type: application/json'); // Set JSON response header
                echo json_encode(['success' => $success]); // Return JSON success response
            } else {
                header('Content-Type: application/json'); // Set JSON response header
                echo json_encode(['success' => false, 'error' => 'Invalid data']); // Return error response
            }
        }
    }
}
