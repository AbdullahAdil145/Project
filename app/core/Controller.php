
<?php
class Movie
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = getenv("omdb_api");
    }

    public function getMovieByTitle($title)
    {
        $url = "http://www.omdbapi.com/?apikey={$this->apiKey}&t=" . urlencode($title);
        $response = file_get_contents($url);
        return json_decode($response, true);
    }

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
            $movies[] = $this->getMovieByTitle($title);
        }
        return $movies;
    }

    public function getMoviesByRating($ratingRange)
    {
        // Define movie collections for different rating ranges
        $movieCollections = [
            '9+' => [
                "The Shawshank Redemption", "The Godfather", "The Dark Knight",
                "The Godfather Part II", "12 Angry Men", "Schindler's List"
            ],
            '8+' => [
                "The Lord of the Rings: The Return of the King", "Pulp Fiction",
                "The Good, the Bad and the Ugly", "Fight Club", "Forrest Gump",
                "The Lord of the Rings: The Fellowship of the Ring"
            ],
            '7+' => [
                "Inception", "The Matrix", "Goodfellas", "One Flew Over the Cuckoo's Nest",
                "Seven", "The Silence of the Lambs", "It's a Wonderful Life", "Life Is Beautiful"
            ],
            '6+' => [
                "The Avengers", "Jurassic Park", "Titanic", "Avatar", "Iron Man",
                "Spider-Man", "The Lion King", "Toy Story", "Finding Nemo", "Shrek"
            ]
        ];

        $titles = $movieCollections[$ratingRange] ?? $movieCollections['9+'];

        $movies = [];
        foreach ($titles as $title) {
            $movie = $this->getMovieByTitle($title);
            if ($movie && isset($movie['imdbRating']) && $movie['imdbRating'] !== 'N/A') {
                $rating = floatval($movie['imdbRating']);
                $minRating = floatval(substr($ratingRange, 0, 1));
                $maxRating = $minRating + 1;

                // Filter movies within the specified range
                if ($rating >= $minRating && ($ratingRange === '9+' ? true : $rating < $maxRating)) {
                    $movies[] = $movie;
                }
            }
        }

        return array_slice($movies, 0, 10); // Limit to 10 movies
    }

    public function searchMoviesByTitle($title, $year = null)
    {
        $url = "http://www.omdbapi.com/?apikey={$this->apiKey}&s=" . urlencode($title);
        if ($year) {
            $url .= "&y=" . urlencode($year);
        }

        $response = file_get_contents($url);
        $data = json_decode($response, true);

        $movies = $data['Search'] ?? [];

        // Remove duplicates based on title and year
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

    public function generateAIReviews($movieTitle, $movieYear, $count = 3)
    {
        $geminiApiKey = getenv("gemini_api");
        if (!$geminiApiKey) {
            return [];
        }

        $randomUsernames = [
            'MovieBuff2024', 'CinemaLover', 'FilmCritic99', 'PopcornAddict', 'ReelReviewer',
            'SilverScreenFan', 'MovieMagic', 'CinephileCorner', 'FilmFanatic', 'TheaterGoer',
            'MovieMaster', 'CinematicSoul', 'FilmJunkie', 'ScreenTime', 'MovieNerd123'
        ];

        $prompt = "Generate $count different movie reviews for '$movieTitle' ($movieYear). Each review should be 2-3 sentences long, unique in perspective, and realistic. Include both positive and mixed opinions. Format as JSON array with 'review' field only. No usernames needed.";

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=" . $geminiApiKey;

        $data = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ]
        ];

        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($data)
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            error_log("Gemini API request failed for movie: $movieTitle");
            // Return fallback reviews if API fails
            return $this->getFallbackReviews($randomUsernames, $count);
        }

        $result = json_decode($response, true);

        if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return [];
        }

        $aiResponse = $result['candidates'][0]['content']['parts'][0]['text'];

        // Clean up the response to extract JSON
        $aiResponse = preg_replace('/```json\s*/', '', $aiResponse);
        $aiResponse = preg_replace('/\s*```/', '', $aiResponse);
        $aiResponse = trim($aiResponse);

        $reviews = json_decode($aiResponse, true);

        if (!is_array($reviews)) {
            // Fallback: try to parse as individual reviews
            $fallbackReviews = [
                ['review' => 'A masterpiece of cinema that deserves all the praise it gets. The storytelling is exceptional and the performances are top-notch.'],
                ['review' => 'Good movie with some great moments, though it has a few pacing issues. Still worth watching for the excellent cinematography.'],
                ['review' => 'An entertaining film that delivers on its promises. The cast chemistry works well and the direction is solid throughout.']
            ];
            $reviews = array_slice($fallbackReviews, 0, $count);
        } else {
            $reviews = array_slice($reviews, 0, $count);
        }

        // Add random usernames and ratings
        $finalReviews = [];
        foreach ($reviews as $index => $review) {
            $finalReviews[] = [
                'username' => $randomUsernames[array_rand($randomUsernames)],
                'review' => $review['review'] ?? $review,
                'rating' => rand(3, 5) // Random rating between 3-5 stars
            ];
        }

        return $finalReviews;
    }

    private function getFallbackReviews($usernames, $count = 3)
    {
        $fallbackReviews = [
            'A captivating film that keeps you engaged from start to finish. The cinematography and performances are truly outstanding.',
            'Great movie with solid acting and an interesting plot. Some pacing issues but overall very entertaining.',
            'An excellent addition to the genre with memorable characters and impressive direction. Highly recommended.',
            'Good film that delivers on its promises. The story development is well-crafted and the ending is satisfying.',
            'Entertaining movie with strong performances from the cast. The visual effects and soundtrack complement the story perfectly.',
            'A well-made film that balances drama and action effectively. The character development is particularly noteworthy.'
        ];

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
