<?php
/**
 * FoodFinder - Global Footer Template
 */
?>

<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <!-- Kolom Brand & Tentang Singkat -->
      <div class="footer-col">
        <a href="<?= BASE_URL ?>index.php" class="brand-logo" style="margin-bottom: 16px;">
          <div class="brand-icon">
            <i class="fas fa-utensils"></i>
          </div>
          <span>FoodFinder<span class="dot">.</span></span>
        </a>
        <p style="color: var(--text-secondary); font-size: 0.92rem; line-height: 1.7; margin-bottom: 20px;">
          Platform pintar pencarian dan rekomendasi tempat makan terbaik untuk mahasiswa dan masyarakat umum. Temukan spot kuliner impianmu dengan mudah dan cepat.
        </p>
        <div style="display: flex; gap: 12px;">
          <a href="#" class="btn-fav" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" class="btn-fav" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
          <a href="#" class="btn-fav" aria-label="GitHub"><i class="fab fa-github"></i></a>
        </div>
      </div>

      <!-- Kolom Menu Navigasi -->
      <div class="footer-col">
        <h5>Navigasi Cepat</h5>
        <ul class="footer-links">
          <li><a href="<?= BASE_URL ?>index.php"><i class="fas fa-chevron-right" style="font-size: 0.75rem;"></i> Beranda</a></li>
          <li><a href="<?= BASE_URL ?>search.php"><i class="fas fa-chevron-right" style="font-size: 0.75rem;"></i> Jelajahi Tempat</a></li>
          <li><a href="<?= BASE_URL ?>recommendation.php"><i class="fas fa-chevron-right" style="font-size: 0.75rem;"></i> Rekomendasi Pintar</a></li>
          <li><a href="<?= BASE_URL ?>favorites.php"><i class="fas fa-chevron-right" style="font-size: 0.75rem;"></i> Tempat Favorit</a></li>
          <li><a href="<?= BASE_URL ?>about.php"><i class="fas fa-chevron-right" style="font-size: 0.75rem;"></i> Tentang FoodFinder</a></li>
        </ul>
      </div>

      <!-- Kolom Kategori Kuliner -->
      <div class="footer-col">
        <h5>Kategori Populer</h5>
        <ul class="footer-links">
          <li><a href="<?= BASE_URL ?>search.php?category=2"><i class="fas fa-mug-hot" style="font-size: 0.75rem;"></i> Kafe & Kopi (WFC)</a></li>
          <li><a href="<?= BASE_URL ?>search.php?category=1"><i class="fas fa-utensils" style="font-size: 0.75rem;"></i> Makanan Berat</a></li>
          <li><a href="<?= BASE_URL ?>search.php?category=3"><i class="fas fa-bowl-rice" style="font-size: 0.75rem;"></i> Makanan Tradisional</a></li>
          <li><a href="<?= BASE_URL ?>search.php?category=6"><i class="fas fa-ice-cream" style="font-size: 0.75rem;"></i> Dessert & Manisan</a></li>
          <li><a href="<?= BASE_URL ?>search.php?category=8"><i class="fas fa-bowl-food" style="font-size: 0.75rem;"></i> Makanan Asia & Jepang</a></li>
        </ul>
      </div>

      <!-- Kolom Info Proyek RPL -->
      <div class="footer-col">
        <h5>Tentang Proyek</h5>
        <div class="footer-contact-item">
          <i class="fas fa-laptop-code" style="color: var(--pink-primary);"></i>
          <span>Tugas Rekayasa Perangkat Lunak (RPL)</span>
        </div>
        <div class="footer-contact-item">
          <i class="fas fa-server" style="color: var(--pink-primary);"></i>
          <span>PHP Native & MySQL (XAMPP Stack)</span>
        </div>
        <div class="footer-contact-item">
          <i class="fas fa-envelope" style="color: var(--pink-primary);"></i>
          <span>support@foodfinder.local</span>
        </div>
        <div class="footer-contact-item">
          <i class="fas fa-map-marker-alt" style="color: var(--pink-primary);"></i>
          <span>Jakarta Selatan, Indonesia</span>
        </div>
      </div>
    </div>

    <!-- Copyright -->
    <div class="footer-bottom">
      <div>
        &copy; <?= date('Y') ?> <strong>FoodFinder</strong>. Hak Cipta Dilindungi Undang-Undang.
      </div>
      <div>
        Dirancang dengan tema <span style="color: var(--pink-primary); font-weight: 600;">Pink & Clean Minimalist</span> untuk kemudahan pengguna.
      </div>
    </div>
  </div>
</footer>

<!-- Toast Notifications Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Main JavaScript -->
<script src="<?= BASE_URL ?>assets/js/script.js"></script>
</body>
</html>
