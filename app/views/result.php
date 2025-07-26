<!DOCTYPE html>
<html>
<head>
    <title>Search Results - Cinemax</title>

    <!-- Link to custom stylesheet -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Top navigation bar -->
    <div class="navbar">
        <!-- Brand/logo section -->
        <div class="brand">
            <img src="https://img.icons8.com/ios-filled/50/ffffff/video.png" alt="logo"/> Cinemax
        </div>

        <!-- Navigation actions: search and dark mode toggle -->
        <div class="nav-actions">
            <a href="index.php" class="nav-btn">Search</a>
            <button id="darkModeToggle" class="nav-btn">🌙</button>
        </div>
    </div>

    <!-- Main content section -->
    <div class="content">
        <!-- Page heading with search query and optional year -->
        <h2 class="page-title">
            🎬 Results for "<?= htmlspecialchars($query) ?>"
            <?php if (!empty($year)) echo " (Year: " . htmlspecialchars($year) . ")"; ?>
        </h2>

        <!-- Check if movies array is not empty -->
        <?php if (!empty($movies)): ?>
            <!-- Grid container for movie cards -->
            <div class="movie-grid">
                <?php foreach ($movies as $movie): ?>
                    <!-- Individual movie card -->
                    <div class="movie-card">
                        <!-- Link to movie details page with title and year as parameters -->
                        <a href="index.php?action=details&title=<?= urlencode($movie['Title']) ?>&year=<?= urlencode($movie['Year']) ?>" class="movie-card-btn">
                            <!-- Movie poster -->
                            <img src="<?= $movie['Poster'] ?>" alt="Poster">

                            <!-- Movie title and year -->
                            <div class="movie-title"><?= $movie['Title'] ?> (<?= $movie['Year'] ?>)</div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Message shown when no results are found -->
            <p>No results found.</p>
        <?php endif; ?>
    </div>

    <!-- JavaScript for dark mode functionality -->
    <script>
        // Wait for DOM to fully load
        document.addEventListener('DOMContentLoaded', function() {
            // Get the dark mode toggle button
            const darkModeToggle = document.getElementById('darkModeToggle');

            // Reference to the body element
            const body = document.body;

            // Get saved theme from localStorage
            const savedTheme = localStorage.getItem('theme');

            // If dark theme is saved, apply it and update button icon
            if (savedTheme === 'dark') {
                body.classList.add('dark');
                darkModeToggle.textContent = '☀️';
            }

            // Add click event listener to toggle button
            darkModeToggle.addEventListener('click', function() {
                // Toggle 'dark' class on body
                body.classList.toggle('dark');

                // If dark mode is active, update icon and save to localStorage
                if (body.classList.contains('dark')) {
                    darkModeToggle.textContent = '☀️';
                    localStorage.setItem('theme', 'dark');
                } else {
                    // If dark mode is disabled, revert icon and update storage
                    darkModeToggle.textContent = '🌙';
                    localStorage.setItem('theme', 'light');
                }
            });
        });
    </script>
</body>
</html>
