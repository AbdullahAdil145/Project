<!DOCTYPE html>
<html>
<head>
    <title>Search a Movie - Cinemax</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="brand">
            <img src="https://img.icons8.com/ios-filled/50/ffffff/video.png" alt="logo"/> Cinemax
        </div>
        <div class="nav-actions">
            <button id="darkModeToggle" class="nav-btn">🌙</button>
        </div>
    </div>

    <!-- Search Section -->
    <div class="content">
        <h2 class="page-title">🎬 Search a Movie</h2>
        <form action="index.php" method="get" class="search-form">
            <input type="hidden" name="action" value="search">
            <input type="text" name="title" placeholder="Enter movie title..." required>
            <input type="text" name="year" placeholder="Year (optional)">
            <button type="submit">Search</button>
        </form>

        <!-- Rating Filter for Homepage -->
        <div class="rating-filter">
            <h3>Filter by IMDb Rating</h3>
            <form action="index.php" method="get" class="rating-form">
                <select name="rating" onchange="this.form.submit()">
                    <option value="">All Ratings</option>
                    <option value="9+" <?= isset($selectedRating) && $selectedRating === '9+' ? 'selected' : '' ?>>9.0+ Rating</option>
                    <option value="8+" <?= isset($selectedRating) && $selectedRating === '8+' ? 'selected' : '' ?>>8.0 - 8.9 Rating</option>
                    <option value="7+" <?= isset($selectedRating) && $selectedRating === '7+' ? 'selected' : '' ?>>7.0 - 7.9 Rating</option>
                    <option value="6+" <?= isset($selectedRating) && $selectedRating === '6+' ? 'selected' : '' ?>>6.0 - 6.9 Rating</option>
                </select>
            </form>
        </div>

        <h2 class="section-title">
            🔥 <?php if (isset($selectedRating) && $selectedRating): ?>
                Movies with <?= htmlspecialchars($selectedRating) ?> Rating
            <?php else: ?>
                Top Rated
            <?php endif; ?>
        </h2>

        <div class="movie-grid">
            <?php foreach ($topMovies as $movie): ?>
                <div class="movie-card">
                    <a href="index.php?action=details&title=<?= urlencode($movie['Title']) ?>&year=<?= urlencode($movie['Year']) ?>" class="movie-card-btn">
                        <img src="<?= $movie['Poster'] ?>" alt="Poster">
                        <div class="movie-title"><?= $movie['Title'] ?></div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<script>
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
</body>
</html>
