
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($movie['Title']) ?> - Cinemax</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="navbar">
        <div class="brand">
            <img src="https://img.icons8.com/ios-filled/50/ffffff/video.png" alt="logo"/> Cinemax
        </div>
        <div class="nav-actions">
            <a href="index.php" class="nav-btn">Search</a>
            <button id="darkModeToggle" class="nav-btn">🌙</button>
        </div>
    </div>

    <div class="content">
        <?php if ($movie && $movie['Response'] !== 'False'): ?>
            <div class="movie-detail-card">
                <img src="<?= $movie['Poster'] !== 'N/A' ? $movie['Poster'] : 'https://via.placeholder.com/300x450?text=No+Image' ?>" alt="<?= htmlspecialchars($movie['Title']) ?>" class="poster">

                <h1><?= htmlspecialchars($movie['Title']) ?> (<?= $movie['Year'] ?>)</h1>

                <div class="movie-info">
                    <p><strong>Director:</strong> <?= htmlspecialchars($movie['Director']) ?></p>
                    <p><strong>Cast:</strong> <?= htmlspecialchars($movie['Actors']) ?></p>
                    <p><strong>Genre:</strong> <?= htmlspecialchars($movie['Genre']) ?></p>
                    <p><strong>Runtime:</strong> <?= htmlspecialchars($movie['Runtime']) ?></p>
                    <p><strong>Rating:</strong> <?= htmlspecialchars($movie['Rated']) ?></p>
                    <p><strong>IMDb Rating:</strong> <?= htmlspecialchars($movie['imdbRating']) ?>/10</p>
                    <p><strong>Release Date:</strong> <?= htmlspecialchars($movie['Released']) ?></p>
                </div>

                <div class="rating-section mt-4">
                    <h3 style="color: #e50914; margin-bottom: 15px;">Rate This Movie</h3>

                    <?php if ($avgRating['count'] > 0): ?>
                    <div class="average-rating mb-3">
                        <strong>Average Rating: <?= $avgRating['average'] ?>/5</strong>
                        <span class="text-muted">(<?= $avgRating['count'] ?> rating<?= $avgRating['count'] > 1 ? 's' : '' ?>)</span>
                    </div>
                    <?php endif; ?>

                    <div class="star-rating" data-movie-title="<?= htmlspecialchars($movie['Title']) ?>" data-movie-year="<?= $movie['Year'] ?>">
                        <i class="fas fa-star star" data-rating="1"></i>
                        <i class="fas fa-star star" data-rating="2"></i>
                        <i class="fas fa-star star" data-rating="3"></i>
                        <i class="fas fa-star star" data-rating="4"></i>
                        <i class="fas fa-star star" data-rating="5"></i>
                    </div>
                    <p class="rating-text mt-2">
                        <?= $userRating ? 'Click a star to change your rating' : 'Click a star to rate this movie' ?>
                    </p>
                    <div class="user-rating mt-2" style="<?= $userRating ? 'display: block;' : 'display: none;' ?>">
                        <strong>Your rating: <span class="rating-value"><?= $userRating ?></span>/5 stars</strong>
                    </div>
                </div>

                <div class="plot">
                    <h3>Plot</h3>
                    <p><?= htmlspecialchars($movie['Plot']) ?></p>
                </div>

                <?php if (!empty($aiReviews)): ?>
                <div class="ai-reviews-section mt-4">
                    <h3 style="color: #e50914; margin-bottom: 20px;">
                        <i class="fas fa-robot"></i> AI Generated Reviews
                    </h3>

                    <?php foreach($aiReviews as $review): ?>
                    <div class="review-card mb-3">
                        <div class="review-header mb-2">
                            <div class="reviewer-info">
                                <strong class="reviewer-name"><?= htmlspecialchars($review['username']) ?></strong>
                                <div class="review-stars">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        <p class="review-text"><?= htmlspecialchars($review['review']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="movie-detail-card">
                <h1>Movie not found</h1>
                <p>Sorry, we couldn't find details for this movie.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dark mode toggle functionality
            const darkModeToggle = document.getElementById('darkModeToggle');
            const body = document.body;

            // Load saved theme
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                body.classList.add('dark');
                darkModeToggle.textContent = '☀️';
            }

            darkModeToggle.addEventListener('click', function() {
                body.classList.toggle('dark');

                if (body.classList.contains('dark')) {
                    darkModeToggle.textContent = '☀️';
                    localStorage.setItem('theme', 'dark');
                } else {
                    darkModeToggle.textContent = '🌙';
                    localStorage.setItem('theme', 'light');
                }
            });

            // Star rating functionality
            const stars = document.querySelectorAll('.star');
            const ratingText = document.querySelector('.rating-text');
            const userRating = document.querySelector('.user-rating');
            const ratingValue = document.querySelector('.rating-value');
            const starContainer = document.querySelector('.star-rating');

            let currentRating = <?= $userRating ? $userRating : 0 ?>;

            // Initialize with existing rating
            if (currentRating > 0) {
                updateStars(currentRating);
                showUserRating(currentRating);
            }

            stars.forEach(star => {
                // Hover effect
                star.addEventListener('mouseenter', function() {
                    const rating = parseInt(this.dataset.rating);
                    highlightStars(rating);
                });

                // Click to rate
                star.addEventListener('click', function() {
                    const rating = parseInt(this.dataset.rating);
                    currentRating = rating;
                    updateStars(rating);
                    saveRatingToDatabase(rating);
                    showUserRating(rating);
                });
            });

            // Reset on mouse leave
            starContainer.addEventListener('mouseleave', function() {
                updateStars(currentRating);
            });

            function highlightStars(rating) {
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.style.color = '#ffc107';
                    } else {
                        star.style.color = '#dee2e6';
                    }
                });
            }

            function updateStars(rating) {
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.style.color = '#ffc107';
                    } else {
                        star.style.color = '#dee2e6';
                    }
                });
            }

            function saveRatingToDatabase(rating) {
                fetch('index.php?action=rate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `title=${encodeURIComponent(starContainer.dataset.movieTitle)}&year=${encodeURIComponent(starContainer.dataset.movieYear)}&rating=${rating}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Rating saved successfully');
                        // Optionally reload the page to update average rating
                        // location.reload();
                    } else {
                        console.error('Failed to save rating');
                        alert('Failed to save rating. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while saving your rating.');
                });
            }

            function showUserRating(rating) {
                ratingValue.textContent = rating;
                userRating.style.display = 'block';
                ratingText.textContent = 'Thanks for rating! Click another star to change your rating.';
            }
        });
    </script>
</body>
</html>
