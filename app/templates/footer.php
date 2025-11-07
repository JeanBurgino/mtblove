    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="bi bi-emoji-laughing"></i> Meme Gallery</h5>
                    <p class="text-muted">Die beste Sammlung der lustigsten Memes</p>
                    <!-- TODO: Social Media Links hier hinzufügen -->
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-1">
                        &copy; <?php echo date('Y'); ?> Meme Gallery
                    </p>
                    <p class="text-muted">
                        <?php if (isLoggedIn()): ?>
                            <small>Eingeloggt als: <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></small>
                        <?php endif; ?>
                    </p>
                    <!-- TODO: Links zu Datenschutz & Impressum -->
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle (inkl. Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- TODO: Später Analytics-Tracking-Code hier einbinden -->
    <!-- TODO: Custom JavaScript für interaktive Features -->

    <script>
        // Einfache Toast-Notification Funktion
        function showToast(message, type = 'info') {
            // TODO: Implementiere schönere Toast-Notifications
            console.log(`[${type.toUpperCase()}] ${message}`);
        }

        // TODO: Lazy Loading für Bilder implementieren
        // TODO: Infinite Scroll für Meme-Gallery
        // TODO: Meme-Modal mit Lightbox-Effekt
    </script>
</body>
</html>
