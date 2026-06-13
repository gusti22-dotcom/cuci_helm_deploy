// CSRF Token untuk AJAX
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';

// Waktu
function updateClock() {
    const now = new Date();
    const timeEl = document.getElementById('topbarTime');
    const dateEl = document.getElementById('topbarDate');
    if (!timeEl) return;

    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    timeEl.textContent = `${hours}:${minutes}:${seconds}`;

    if (dateEl) {
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        dateEl.textContent = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
    }
}
setInterval(updateClock, 1000);
updateClock();

// SIDEBAR TOGGLE
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');

if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
        sidebar?.classList.toggle('open');
        overlay?.classList.toggle('active');
    });
}
if (overlay) {
    overlay.addEventListener('click', () => {
        sidebar?.classList.remove('open');
        overlay.classList.remove('active');
    });
}

// ALERT AUTO DISMISS
const alertMsg = document.getElementById('alertMsg');
if (alertMsg) {
    setTimeout(() => {
        alertMsg.style.transition = 'opacity 0.4s, transform 0.4s';
        alertMsg.style.opacity = '0';
        alertMsg.style.transform = 'translateY(-8px)';
        setTimeout(() => alertMsg.remove(), 400);
    }, 4000);
}

// GLOBAL CONFIRM MODAL
const confirmModal = document.getElementById('confirmModal');
const modalTitle = document.getElementById('modalTitle');
const modalText = document.getElementById('modalText');
const modalConfirm = document.getElementById('modalConfirm');
const modalCancel = document.getElementById('modalCancel');

let confirmCallback = null;

window.showConfirm = function(title, text, onConfirm, btnClass = 'btn-primary') {
    if (!confirmModal) return;
    modalTitle.textContent = title;
    modalText.textContent = text;
    modalConfirm.className = `btn ${btnClass}`;
    confirmCallback = onConfirm;
    confirmModal.classList.add('active');
};

if (modalCancel) {
    modalCancel.addEventListener('click', () => {
        confirmModal.classList.remove('active');
        confirmCallback = null;
    });
}
if (modalConfirm) {
    modalConfirm.addEventListener('click', () => {
        if (confirmCallback) confirmCallback();
        confirmModal.classList.remove('active');
        confirmCallback = null;
    });
}
if (confirmModal) {
    confirmModal.addEventListener('click', (e) => {
        if (e.target === confirmModal) {
            confirmModal.classList.remove('active');
            confirmCallback = null;
        }
    });
}

// TOAST NOTIFICATIONS
// Membuat toast container
const toastContainer = document.createElement('div');
toastContainer.id = 'toast-container';
document.body.appendChild(toastContainer);

window.showToast = function(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    const icon = type === 'success'
        ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>'
        : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';

    toast.innerHTML = `${icon}<span>${message}</span>`;
    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.style.transition = 'opacity 0.3s, transform 0.3s';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
};

// AJAX HELPER
window.ajaxRequest = async function(url, method = 'GET', data = null) {
    const options = {
        method,
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    };
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    const response = await fetch(url, options);
    return response.json();
};

// FORMAT CURRENCY
window.formatRupiah = function(num) {
    return 'Rp ' + parseInt(num).toLocaleString('id-ID');
};

// SERVICE CARD SELECTION
document.querySelectorAll('.service-card-select').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.service-card-select').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        const hiddenInput = document.getElementById('selected_layanan_id');
        const hiddenName = document.getElementById('selected_layanan_name');
        if (hiddenInput) hiddenInput.value = this.dataset.id;
        if (hiddenName) hiddenName.value = this.dataset.name;
        // Update price display
        const priceDisplay = document.getElementById('selected_price_display');
        if (priceDisplay) {
            priceDisplay.textContent = 'Layanan: ' + this.dataset.name + ' - ' + formatRupiah(this.dataset.price);
        }
    });
});

// BADGE PROSES COUNT
function updateProseBadge() {
    const badge = document.getElementById('badge-proses');
    if (!badge) return;
    fetch('/api/count-proses', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.count > 0) {
            badge.textContent = data.count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    })
    .catch(() => {});
}
updateProseBadge();

// NUMBER FORMATTING INPUT
document.querySelectorAll('input[data-type="number"]').forEach(input => {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});

// CHART BAR ANIMATION
function animateCharts() {
    document.querySelectorAll('.chart-bar[data-percent]').forEach(bar => {
        const pct = parseFloat(bar.dataset.percent) || 0;
        bar.style.height = '0%';
        setTimeout(() => {
            bar.style.height = pct + '%';
        }, 100);
    });
}
animateCharts();
