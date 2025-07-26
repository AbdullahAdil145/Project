<?php
class Movie
{
    private $apiKey;

    // Constructor: fetch OMDb API key from environment
    public function __construct()
    {
        $this->apiKey = getenv("omdb_api");
    }

    // Fetch a movie's full details by title using OMDb API
    public function getMovieByTitle($title)
    {
        $url = "http://www.omdbapi.com/?apikey={$this->apiKey}&t=" . urlencode($title);
        $response = file_get_contents($url);
        return json_decode($response, true);
    }

    // Return predefined list of top-rated movies
    public function getTopRated()
    {
        $topTitles = [
            "The Shawshank Redemption", "The Godfather", "The Dark Knight",
            "The Godfather Part II", "12 Angry Men", "Schindler's List",
            "The Lord of the Rings: The Return of the King", "Pulp Fiction",
            "The Good, the Bad and the Ugly", "Fight Club"
        ];

        $movies = [];
        foreach ($topTitles as $title) {
            $movies[] = $this->getMovieByTitle($title); // Fetch each movie's details
        }
        return $movies;
    }

    // Return movies from a specific rating range (e.g. 8+, 9+)
    public function getMoviesByRating($ratingRange)
    {
        // Map rating ranges to title groups
        $movieCollections = [
            '9+' => [ ... ],
            '8+' => [ ... ],
            '7+' => [ ... ],
            '6+' => [ ... ]
        ];

        $titles = $movieCollections[$ratingRange] ?? $movieCollections['9+']; // Fallback to 9+ if invalid

        $movies = [];
        foreach ($titles as $title) {
            $movie = $this->getMovieByTitle($title);
            if ($movie && isset($movie['imdbRating']) && $movie['imdbRating'] !== 'N/A') {
                $rating = floatval($movie['imdbRating']);
                $minRating = floatval(substr($ratingRange, 0, 1));
                $maxRating = $minRating + 1;

                // Filter movies based on exact rating range
                if ($rating >= $minRating && ($ratingRange === '9+' ? true : $rating < $maxRating)) {
                    $movies[] = $movie;
                }
            }
        }

        return array_slice($movies, 0, 10); // Limit results to 10 movies
    }

    // Search for movies by title and optional year
    public function searchMoviesByTitle($title, $year = null)
    {
        $url = "http://www.omdbapi.com/?apikey={$this->apiKey}&s=" . urlencode($title);
        if ($year) {
            $url .= "&y=" . urlencode($year);
        }

        $response = file_get_contents($url);
        $data = json_decode($response, true);
        $movies = $data['Search'] ?? [];

        // Eliminate duplicate entries by title and year
        $uniqueMovies = [];
        $seen = [];

        foreach ($movies as $movie) {
            $key = $movie['Title'] . '_' . $movie['Year'];
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $uniqueMovies[] = $movie;
            }
        }

        return $uniqueMovies;
    }

    // Save or update user's rating for a specific movie
    public function saveRating($movieTitle, $movieYear, $userSession, $rating)
    {
        global $db;

        try {
            $stmt = $db->prepare("
                INSERT INTO movie_ratings (movie_title, movie_year, user_session, rating) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE rating = ?, updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$movieTitle, $movieYear, $userSession, $rating, $rating]);
            return true;
        } catch (PDOException $e) {
            error_log("Failed to save rating: " . $e->getMessage());
            return false;
        }
    }

    // Fetch user's individual rating for a specific movie
    public function getRating($movieTitle, $movieYear, $userSession)
    {
        global $db;

        try {
            $stmt = $db->prepare("
                SELECT rating FROM movie_ratings 
                WHERE movie_title = ? AND movie_year = ? AND user_session = ?
            ");
            $stmt->execute([$movieTitle, $movieYear, $userSession]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['rating'] : null;
        } catch (PDOException $e) {
            error_log("Failed to get rating: " . $e->getMessage());
            return null;
        }
    }

    // Calculate average rating and count for a specific movie
    public function getAverageRating($movieTitle, $movieYear)
    {
        global $db;

        try {
            $stmt = $db->prepare("
                SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings 
                FROM movie_ratings 
                WHERE movie_title = ? AND movie_year = ?
            ");
            $stmt->execute([$movieTitle, $movieYear]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'average' => $result['avg_rating'] ? round($result['avg_rating'], 1) : null,
                'count' => $result['total_ratings']
            ];
        } catch (PDOException $e) {
            error_log("Failed to get average rating: " . $e->getMessage());
            return ['average' => null, 'count' => 0];
        }
    }

    // Generate AI-based reviews using Gemini API
    public function generateAIReviews($movieTitle, $movieYear, $count = 3)
    {
        $geminiApiKey = getenv("gemini_api");
        if (!$geminiApiKey) {
            return [];
        }

        // Random usernames for reviews
        $randomUsernames = [ ... ];

        // Prompt to send to Gemini API
        $prompt = "Generate $count different movie reviews for '$movieTitle' ($movieYear)...";

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=" . $geminiApiKey;

        $data = [
            'contents' => [[ 'parts' => [[ 'text' => $prompt ]] ]]
        ];

        // Prepare HTTP POST request
        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($data)
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        // If API call fails, use fallback
        if ($response === false) {
            error_log("Gemini API request failed for movie: $movieTitle");
            return $this->getFallbackReviews($randomUsernames, $count);
        }

        $result = json_decode($response, true);

        if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return [];
        }

        $aiResponse = $result['candidates'][0]['content']['parts'][0]['text'];

        // Remove formatting wrappers
        $aiResponse = preg_replace('/```json\s*/', '', $aiResponse);
        $aiResponse = preg_replace('/\s*```/', '', $aiResponse);
        $aiResponse = trim($aiResponse);

        $reviews = json_decode($aiResponse, true);

        // Use fallback if parsing fails
        if (!is_array($reviews)) {
            $fallbackReviews = [
                ['review' => 'A masterpiece...'],
                ['review' => 'Good movie with...'],
                ['review' => 'An entertaining film...']
            ];
            $reviews = array_slice($fallbackReviews, 0, $count);
        } else {
            $reviews = array_slice($reviews, 0, $count);
        }

        // Attach random usernames and ratings
        $finalReviews = [];
        foreach ($reviews as $index => $review) {
            $finalReviews[] = [
                'username' => $randomUsernames[array_rand($randomUsernames)],
                'review' => $review['review'] ?? $review,
                'rating' => rand(1, 5) // Random rating from 3 to 5
            ];
        }

        return $finalReviews;
    }

    // Fallback reviews if Gemini API fails
    private function getFallbackReviews($usernames, $count = 3)
    {
        $fallbackReviews = [ ... ];

        $finalReviews = [];
        $selectedReviews = array_rand($fallbackReviews, min($count, count($fallbackReviews)));

        if (!is_array($selectedReviews)) {
            $selectedReviews = [$selectedReviews];
        }

        foreach ($selectedReviews as $index) {
            $finalReviews[] = [
                'username' => $usernames[array_rand($usernames)],
                'review' => $fallbackReviews[$index],
                'rating' => rand(3, 5)
            ];
        }

        return $finalReviews;
    }
}
