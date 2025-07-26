
<?php
require_once 'app/core/Controller.php';
require_once 'app/models/Movie.php';

class MovieController extends Controller
{
    public function index()
    {
        $movieModel = new Movie();
        $rating = $_GET['rating'] ?? null;

        if ($rating) {
            $topMovies = $movieModel->getMoviesByRating($rating);
        } else {
            $topMovies = $movieModel->getTopRated();
        }

        $this->view('search', ['topMovies' => $topMovies, 'selectedRating' => $rating]);
    }

    public function search()
    {
        $title = $_GET['title'] ?? '';
        $year = $_GET['year'] ?? null;

        if ($title) {
            $movieModel = new Movie();
            $movies = $movieModel->searchMoviesByTitle($title, $year);
            $this->view('result', ['movies' => $movies, 'query' => $title, 'year' => $year]);
        } else {
            header("Location: index.php");
        }
    }

    public function details()
    {
        $title = $_GET['title'] ?? '';
        $year = $_GET['year'] ?? null;

        if ($title) {
            $movieModel = new Movie();
            $movie = $movieModel->getMovieByTitle($title);
            $aiReviews = $movieModel->generateAIReviews($title, $movie['Year'] ?? $year);

            // Get user session ID
            $userSession = session_id();

            // Get user's existing rating
            $userRating = $movieModel->getRating($title, $movie['Year'] ?? $year, $userSession);

            // Get average rating and count
            $avgRating = $movieModel->getAverageRating($title, $movie['Year'] ?? $year);

            $this->view('details', [
                'movie' => $movie, 
                'aiReviews' => $aiReviews,
                'userRating' => $userRating,
                'avgRating' => $avgRating
            ]);
        } else {
            header("Location: index.php");
        }
    }

    public function rate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $year = $_POST['year'] ?? '';
            $rating = $_POST['rating'] ?? 0;

            if ($title && $year && $rating >= 1 && $rating <= 5) {
                $movieModel = new Movie();
                $userSession = session_id();

                $success = $movieModel->saveRating($title, $year, $userSession, $rating);

                header('Content-Type: application/json');
                echo json_encode(['success' => $success]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Invalid data']);
            }
        }
    }
}
