<!DOCTYPE html>
<html>
<head>
    <title>Search a Movie - Cinemax</title>

    <!-- Link to external stylesheet -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <!-- Brand/logo -->
        <div class="brand">
            <img src="https://img.icons8.com/ios-filled/50/ffffff/video.png" alt="logo"/> Cinemax
        </div>

        <!-- Dark mode toggle -->
        <div class="nav-actions">
            <button id="darkModeToggle" class="nav-btn">🌙</button>
        </div>
    </div>

    <!-- Main content area -->
    <div class="content">
        <!-- Page title -->
        <h2 class="page-title">🎬 Search a Movie</h2>

        <!-- Search form -->
        <form action="index.php" method="get" class="search-form">
            <input type="hidden" name="action" value="search">
            <input type="text" name="title" placeholder="Enter movie title..." required>
            <input type="text" name="year" placeholder="Year (optional)">
            <button type="submit">Search</button>
        </form>

        <!-- IMDb rating filter -->
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

        <!-- Section title based on selected rating -->
        <h2 class="section-title">
            🔥 <?php if (isset($selectedRating) && $selectedRating): ?>
                <!-- Display selected rating label -->
                Movies with <?= htmlspecialchars($selectedRating) ?> Rating
            <?php else: ?>
                <!-- Default label -->
                Top Rated
            <?php endif; ?>
        </h2>

        <!-- Grid of top or filtered movies -->
        <div class="movie-grid">
            <?php foreach ($topMovies as $movie): ?>
                <div class="movie-card">
                    <!-- Movie detail link -->
                    <a href="index.php?action=details&title=<?= urlencode($movie['Title']) ?>&year=<?= urlencode($movie['Year']) ?>" class="movie-card-btn">
                        <!-- Poster image -->
                        <img src="<?= $movie['Poster'] ?>" alt="Poster">
                        <!-- Movie title -->
                        <div class="movie-title"><?= $movie['Title'] ?></div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Dark mode toggle script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get DOM elements
            const darkModeToggle = document.getElementById('darkModeToggle');
            const body = document.body;

            // Load saved theme from localStorage
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                body.classList.add('dark');
                darkModeToggle.textContent = '☀️';
            }

            // Handle toggle button click
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
