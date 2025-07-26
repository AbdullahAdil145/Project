<!DOCTYPE html>
<html>
<head>
    <title>Search Results - Cinemax</title>
    <link rel="stylesheet" href="style.css">
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
        <h2 class="page-title">
            🎬 Results for "<?= htmlspecialchars($query) ?>"
            <?php if (!empty($year)) echo " (Year: " . htmlspecialchars($year) . ")"; ?>
        </h2>

        <?php if (!empty($movies)): ?>
            <div class="movie-grid">
                <?php foreach ($movies as $movie): ?>
                    <div class="movie-card">
                        <a href="index.php?action=details&title=<?= urlencode($movie['Title']) ?>&year=<?= urlencode($movie['Year']) ?>" class="movie-card-btn">
                            <img src="<?= $movie['Poster'] ?>" alt="Poster">
                            <div class="movie-title"><?= $movie['Title'] ?> (<?= $movie['Year'] ?>)</div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No results found.</p>
        <?php endif; ?>
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
