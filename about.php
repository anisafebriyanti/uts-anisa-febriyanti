<?php
/**
 * FoodFinder - Halaman Tentang Kami (About)
 */

$pageTitle = 'Tentang FoodFinder';
$pageDesc = 'Mengenal FoodFinder, visi & misi kami dalam memudahkan pencarian tempat makan terbaik untuk mahasiswa dan masyarakat.';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 40px; padding-bottom: 80px;">
  
  <!-- Hero Section Tentang -->
  <div style="text-align: center; max-width: 750px; margin: 0 auto 50px;">
    <div class="hero-tag">
      <i class="fas fa-heart text-danger"></i> Kisah di Balik FoodFinder
    </div>
    <h1 class="hero-title" style="font-size: 2.8rem; margin-bottom: 16px;">
      Membantu Kamu Menemukan <br>
      <span class="highlight">Tempat Makan Paling Pas</span>
    </h1>
    <p class="hero-desc" style="margin: 0 auto; font-size: 1.05rem;">
      FoodFinder lahir dari pengalaman nyata mahasiswa yang seringkali bingung menentukan tempat makan siang ramah kantong atau mencari kafe yang nyaman untuk menyelesaikan tugas kuliah.
    </p>
  </div>

  <!-- Nilai Utama / Visi Misi -->
  <div class="features-grid" style="margin-bottom: 60px;">
    <div class="feature-box">
      <div class="feature-box-icon">
        <i class="fas fa-bullseye"></i>
      </div>
      <h4>Visi Kami</h4>
      <p>Menjadi platform direktori dan rekomendasi kuliner nomor satu yang paling ramah pengguna, transparan, dan terpercaya bagi generasi muda.</p>
    </div>

    <div class="feature-box">
      <div class="feature-box-icon">
        <i class="fas fa-rocket"></i>
      </div>
      <h4>Misi Kami</h4>
      <p>Menyajikan data tempat makan yang terverifikasi, menyediakan filter cerdas sesuai isi dompet, serta mendukung UMKM kuliner lokal agar lebih dikenal.</p>
    </div>

    <div class="feature-box">
      <div class="feature-box-icon">
        <i class="fas fa-hand-holding-heart"></i>
      </div>
      <h4>Komitmen Kejujuran</h4>
      <p>Seluruh rating dan ulasan bersumber langsung dari pengunjung nyata tanpa rekayasa statistik atau manipulasi algoritma.</p>
    </div>
  </div>

  <!-- Detail Spesifikasi Proyek RPL -->
  <div class="detail-card-panel" style="margin-bottom: 60px;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center;">
      <div>
        <div class="section-tag"><i class="fas fa-code"></i> Arsitektur & Teknologi</div>
        <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 16px;">
          Dibangun dengan Fondasi Web Standar Industri
        </h2>
        <p style="color: var(--text-secondary); line-height: 1.8; margin-bottom: 20px;">
          Proyek perangkat lunak ini dikembangkan secara modular menggunakan <strong>PHP Native</strong> tanpa framework berat, <strong>MySQL</strong> dengan relasi integritas referensial (foreign keys), serta antarmuka modern responsif bertema <strong>Pink & White Clean Minimalist</strong>.
        </p>
        <div style="display: flex; flex-direction: column; gap: 10px;">
          <div style="display: flex; align-items: center; gap: 10px; font-size: 0.95rem;">
            <i class="fas fa-check-circle" style="color: var(--pink-primary);"></i>
            <span>Prepared Statements PDO untuk proteksi penuh SQL Injection.</span>
          </div>
          <div style="display: flex; align-items: center; gap: 10px; font-size: 0.95rem;">
            <i class="fas fa-check-circle" style="color: var(--pink-primary);"></i>
            <span>Enkripsi kata sandi kuat menggunakan <code>password_hash(BCRYPT)</code>.</span>
          </div>
          <div style="display: flex; align-items: center; gap: 10px; font-size: 0.95rem;">
            <i class="fas fa-check-circle" style="color: var(--pink-primary);"></i>
            <span>Desain antarmuka fleksibel di smartphone, tablet, maupun komputer.</span>
          </div>
        </div>
      </div>

      <div style="background: var(--pink-subtle); border: 1px solid var(--pink-border); border-radius: var(--radius-xl); padding: 32px;">
        <h3 style="font-size: 1.3rem; margin-bottom: 18px; color: var(--pink-dark);">
          <i class="fas fa-graduation-cap"></i> Informasi Pengembang
        </h3>
        <p style="font-size: 0.92rem; color: var(--text-secondary); margin-bottom: 16px;">
          Proyek ini disusun untuk memenuhi tugas besar mata kuliah <strong>Rekayasa Perangkat Lunak (RPL)</strong>.
        </p>
        <ul style="list-style: none; display: flex; flex-direction: column; gap: 12px; font-size: 0.92rem;">
          <li><strong>Mata Kuliah:</strong> Rekayasa Perangkat Lunak</li>
          <li><strong>Teknologi:</strong> PHP 8+, MySQL 8+, XAMPP Stack, JavaScript Vanilla, CSS3</li>
          <li><strong>Versi Rilis:</strong> v1.0.0 (Production Ready)</li>
          <li><strong>Status Server:</strong> Apache & MySQL Localhost Terhubung</li>
        </ul>
      </div>
    </div>
  </div>

  <!-- FAQ (Pertanyaan Umum) -->
  <div style="max-width: 800px; margin: 0 auto;">
    <div style="text-align: center; margin-bottom: 36px;">
      <div class="section-tag"><i class="fas fa-circle-question"></i> FAQ</div>
      <h2 class="section-title">Pertanyaan yang Sering Diajukan</h2>
    </div>

    <div style="display: flex; flex-direction: column; gap: 16px;">
      <div class="detail-card-panel" style="margin-bottom: 0; padding: 22px 28px;">
        <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin-bottom: 8px;">
          Bagaimana cara kerja fitur rekomendasi FoodFinder?
        </h4>
        <p style="color: var(--text-secondary); font-size: 0.92rem; line-height: 1.6; margin: 0;">
          Fitur rekomendasi menggunakan algoritma pembobotan preferensi multi-faktor yang membandingkan batas anggaran (budget) maksimal, preferensi kategori makanan, rating pengunjung, dan kecocokan suasana tempat makan yang Anda pilih.
        </p>
      </div>

      <div class="detail-card-panel" style="margin-bottom: 0; padding: 22px 28px;">
        <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin-bottom: 8px;">
          Apakah saya harus mendaftar akun untuk mencari tempat makan?
        </h4>
        <p style="color: var(--text-secondary); font-size: 0.92rem; line-height: 1.6; margin: 0;">
          Tidak. Anda dapat bebas menjelajahi direktori, mencari tempat makan, dan melihat rekomendasi tanpa akun. Namun, memiliki akun memungkinkan Anda menyimpan daftar favorit dan membagikan ulasan ulasan.
        </p>
      </div>

      <div class="detail-card-panel" style="margin-bottom: 0; padding: 22px 28px;">
        <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin-bottom: 8px;">
          Bagaimana cara pemilik tempat makan mendaftarkan usahanya?
        </h4>
        <p style="color: var(--text-secondary); font-size: 0.92rem; line-height: 1.6; margin: 0;">
          Saat ini seluruh kurasi tempat makan dikelola langsung oleh tim Administrator melalui panel admin FoodFinder untuk memastikan validitas data dan foto.
        </p>
      </div>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
