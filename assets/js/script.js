/**
 * FoodFinder - Main JavaScript
 * Interaktivitas Front-end, AJAX Favorit, Toast, dan Filter
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. Mobile Navigation Toggle
  const navToggle = document.getElementById('navToggle');
  const navMenu = document.getElementById('navMenu');

  if (navToggle && navMenu) {
    navToggle.addEventListener('click', function () {
      navMenu.classList.toggle('active');
      const icon = navToggle.querySelector('i');
      if (icon) {
        icon.classList.toggle('fa-bars');
        icon.classList.toggle('fa-times');
      }
    });
  }

  // 2. User Dropdown Menu
  const userTrigger = document.getElementById('userDropdownTrigger');
  const userDropdown = document.getElementById('userDropdownMenu');

  if (userTrigger && userDropdown) {
    userTrigger.addEventListener('click', function (e) {
      e.stopPropagation();
      userDropdown.classList.toggle('show');
    });

    document.addEventListener('click', function (e) {
      if (!userDropdown.contains(e.target) && !userTrigger.contains(e.target)) {
        userDropdown.classList.remove('show');
      }
    });
  }

  // 3. AJAX Toggle Favorite Button
  const favoriteButtons = document.querySelectorAll('.btn-fav-toggle');
  favoriteButtons.forEach(button => {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();

      const restaurantId = this.getAttribute('data-id');
      if (!restaurantId) return;

      const heartIcon = this.querySelector('i');
      const baseUrl = window.FOODFINDER_BASE_URL || './';

      // Kirim request AJAX
      fetch(baseUrl + 'ajax/toggle-favorite.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'restaurant_id=' + encodeURIComponent(restaurantId)
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          if (data.action === 'added') {
            this.classList.add('active');
            if (heartIcon) {
              heartIcon.className = 'fas fa-heart text-danger';
            }
            showToast('Berhasil ditambahkan ke Favorit! ❤️', 'fa-heart');
          } else {
            this.classList.remove('active');
            if (heartIcon) {
              heartIcon.className = 'far fa-heart';
            }
            showToast('Dihapus dari daftar Favorit.', 'fa-info-circle');

            // Jika di halaman favorit, hapus kartu secara animasi
            const favoriteCard = this.closest('.favorite-item-card');
            if (favoriteCard) {
              favoriteCard.style.transition = 'all 0.4s ease';
              favoriteCard.style.opacity = '0';
              favoriteCard.style.transform = 'scale(0.9)';
              setTimeout(() => {
                favoriteCard.remove();
                const remaining = document.querySelectorAll('.favorite-item-card');
                if (remaining.length === 0) {
                  location.reload();
                }
              }, 400);
            }
          }
        } else if (data.status === 'unauthorized') {
          showToast('Silakan login untuk menyimpan tempat makan favorit.', 'fa-lock');
          setTimeout(() => {
            window.location.href = baseUrl + 'login.php';
          }, 1200);
        } else {
          showToast(data.message || 'Terjadi kesalahan.', 'fa-exclamation-circle');
        }
      })
      .catch(error => {
        console.error('Favorite error:', error);
        showToast('Gagal memproses favorit. Periksa koneksi Anda.', 'fa-exclamation-triangle');
      });
    });
  });

  // 4. Quick Demo Login Helper
  window.fillDemoLogin = function(email, password) {
    const emailField = document.getElementById('loginEmail');
    const passField = document.getElementById('loginPassword');
    if (emailField && passField) {
      emailField.value = email;
      passField.value = password;
      showToast('Kredensial demo terisi otomatis! Silakan klik Masuk.', 'fa-magic');
    }
  };

  // 5. Star Rating Selector Helper (Review Form)
  const starInputs = document.querySelectorAll('.star-rating-input input');
  const ratingText = document.getElementById('selectedRatingText');
  
  if (starInputs.length > 0 && ratingText) {
    const starLabels = {
      '5': 'Luar Biasa (5/5)',
      '4': 'Sangat Bagus (4/5)',
      '3': 'Cukup Baik (3/5)',
      '2': 'Kurang Memuaskan (2/5)',
      '1': 'Mengecewakan (1/5)'
    };
    starInputs.forEach(input => {
      input.addEventListener('change', function () {
        ratingText.textContent = starLabels[this.value] || '';
      });
    });
  }

  // 6. Delete Confirmation Modal
  const deleteTriggers = document.querySelectorAll('.btn-confirm-delete');
  const deleteModal = document.getElementById('deleteConfirmModal');
  const deleteForm = document.getElementById('deleteConfirmForm');
  const deleteItemName = document.getElementById('deleteItemName');
  const cancelModalBtn = document.getElementById('cancelDeleteModalBtn');

  if (deleteTriggers.length > 0 && deleteModal && deleteForm) {
    deleteTriggers.forEach(btn => {
      btn.addEventListener('click', function () {
        const actionUrl = this.getAttribute('data-action');
        const itemName = this.getAttribute('data-name') || 'item ini';
        if (deleteForm) deleteForm.action = actionUrl;
        if (deleteItemName) deleteItemName.textContent = itemName;
        deleteModal.classList.add('active');
      });
    });

    if (cancelModalBtn) {
      cancelModalBtn.addEventListener('click', function () {
        deleteModal.classList.remove('active');
      });
    }

    deleteModal.addEventListener('click', function (e) {
      if (e.target === deleteModal) {
        deleteModal.classList.remove('active');
      }
    });
  }
});

/**
 * Toast Notification Helper
 */
function showToast(message, iconClass = 'fa-check-circle') {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.innerHTML = `
    <i class="fas ${iconClass} toast-icon"></i>
    <span>${message}</span>
  `;

  container.appendChild(toast);

  // Trigger animation
  setTimeout(() => toast.classList.add('show'), 20);

  // Remove toast after 3.5 seconds
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 400);
  }, 3500);
}
