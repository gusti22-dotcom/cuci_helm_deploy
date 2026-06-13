@extends('layouts.app')
@section('content')
<div class="page-header">
    <h1 class="page-title">Dashboard <span>Kasir</span></h1>
    <p class="page-desc">
        Kelola pesanan cuci helm hari ini
        @if($lastVisit)
            &nbsp;·&nbsp; Kunjungan terakhir: <span style="color:var(--primary)">{{ $lastVisit }}</span>
        @endif
    </p>
</div>

<!-- Stat Cards -->
<div class="stat-grid">
    <div class="stat-card orange">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
        </div>
        <div class="stat-value" id="statPesanan">{{ $totalPesananHariIni }}</div>
        <div class="stat-label">Pesanan Hari Ini</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </div>
        <div class="stat-value" style="font-size:20px" id="statPendapatan">Rp {{ number_format($totalPendapatanHariIni, 0, ',', '.') }}</div>
        <div class="stat-label">Pendapatan Hari Ini</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
        <div class="stat-value" id="statProses">{{ $totalProses }}</div>
        <div class="stat-label">Sedang Diproses</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <div class="stat-value" id="statSelesai">{{ $totalSelesai }}</div>
        <div class="stat-label">Selesai Hari Ini</div>
    </div>
</div>

<!-- Grid: Form + Table -->
<div style="display:grid; grid-template-columns: 360px 1fr; gap:20px; align-items:start;">
    <!-- Input Pesanan Card -->
    <div class="card" style="position:sticky; top:80px;">
        <div class="card-header">
            <div>
                <div class="card-title">Input Pesanan</div>
                <div class="card-subtitle">Tambah pesanan baru</div>
            </div>
            <div class="stat-icon" style="background:rgba(255,107,43,0.1);color:var(--primary);margin:0;width:36px;height:36px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
            </div>
        </div>
        <div class="card-body">
            <div id="pesananFormWrapper">
                <div class="form-group">
                    <label class="form-label">Nama Customer</label>
                    <input type="text" class="form-control" id="nama_customer" placeholder="Masukkan nama customer..." autocomplete="off">
                </div>
                <div class="form-group">
                    <label class="form-label">Jumlah Helm</label>
                    <input type="number" class="form-control" id="jumlah_helm" placeholder="1" min="1" max="50" value="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Pilih Layanan</label>
                    <div class="service-grid" id="serviceCardGrid">
                        @forelse($layanan as $l)
                            <div class="service-card-select"
                                 data-id="{{ $l->id }}"
                                 data-name="{{ $l->nama_layanan }}"
                                 data-price="{{ $l->harga }}">
                                <div class="service-card-name">{{ $l->nama_layanan }}</div>
                                <div class="service-card-price">Rp {{ number_format($l->harga, 0, ',', '.') }}</div>
                            </div>
                        @empty
                            <p style="color:var(--text-muted);font-size:13px;grid-column:1/-1">
                                Belum ada layanan. Tambah di menu Layanan.
                            </p>
                        @endforelse
                    </div>
                    <input type="hidden" id="selected_layanan_id">
                </div>

                <div id="selected_price_display" style="font-size:12px;color:var(--primary);margin-bottom:14px;min-height:16px;font-weight:600;"></div>

                <div style="display:flex;gap:8px;">
                    <button type="button" class="btn btn-ghost" onclick="resetForm()" style="flex:1">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/>
                        </svg>
                        Reset
                    </button>
                    <button type="button" class="btn btn-primary" id="btnSimpan" style="flex:2" onclick="submitPesanan()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Simpan Pesanan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pesanan Table -->
    <div>
        <!-- Filter -->
        <div class="card mb-4">
            <div class="filter-bar" style="border-radius:var(--radius-lg) var(--radius-lg) 0 0;">
                <div class="filter-group">
                    <span class="filter-label">Filter Nama</span>
                    <input type="text" class="form-control" id="filterNama" placeholder="Cari nama customer..." value="{{ $filterNama }}">
                </div>
                <div class="filter-group">
                    <span class="filter-label">Filter Layanan</span>
                    <select class="form-control" id="filterLayanan">
                        <option value="">Semua Layanan</option>
                        @foreach($layanan as $l)
                            <option value="{{ $l->id }}" {{ $filterLayanan == $l->id ? 'selected' : '' }}>{{ $l->nama_layanan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="btn btn-primary" onclick="applyFilter()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        Cari
                    </button>
                    <button class="btn btn-ghost" onclick="resetFilter()">Reset</button>
                </div>
            </div>
            <div class="table-wrapper">
                <table id="pesananTable">
                    <thead>
                        <tr>
                            <th style="width:44px">No</th>
                            <th>Nama Customer</th>
                            <th>Layanan</th>
                            <th>Jumlah</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                            <th style="width:100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="pesananTbody">
                        @forelse($pesananHariIni as $i => $p)
                            <tr data-id="{{ $p->id }}">
                                <td style="color:var(--text-muted)">{{ $i + 1 }}</td>
                                <td>
                                    <span style="font-weight:600">{{ $p->nama_customer }}</span>
                                    <br><span style="font-size:11px;color:var(--text-muted)">{{ $p->created_at->format('H:i') }}</span>
                                </td>
                                <td>{{ $p->nama_layanan }}</td>
                                <td>{{ $p->jumlah_helm }} helm</td>
                                <td style="font-weight:700;color:var(--primary);font-family:var(--font-display)">
                                    Rp {{ number_format($p->total_harga, 0, ',', '.') }}
                                </td>
                                <td>
                                    <span class="badge {{ $p->status === 'selesai' ? 'badge-success' : 'badge-warning' }}">
                                        {{ ucfirst($p->status) }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-xs {{ $p->status === 'selesai' ? 'btn-ghost' : 'btn-success' }}"
                                            onclick="toggleStatus({{ $p->id }}, this)">
                                        {{ $p->status === 'selesai' ? 'Proses' : 'Selesai' }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyRow">
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-text">Belum ada pesanan hari ini</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<footer>
    <div class="footer-brand">Cuci<span>Helm</span> Pro</div>
    <div class="footer-copy">© {{ date('Y') }} Sistem Kasir Jasa Cuci Helm</div>
    <div class="footer-tag">Dibuat dengan <span>tujuan</span> untuk kemudahan kasir</div>
</footer>

@push('scripts')
<script>
const STORE_URL      = "{{ route('pesanan.store') }}";
const STATUS_URL_BASE = "{{ url('/pesanan') }}";
const STATS_URL      = "{{ route('api.dashboard-stats') }}";

// FUNGSI UPDATE STATS REALTIME (tanpa reload)
async function refreshStats() {
    try {
        const res  = await fetch(STATS_URL, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        // Update angka dengan animasi singkat
        animateValue('statPesanan',    parseInt(document.getElementById('statPesanan').textContent),    data.total_pesanan, 400);
        animateValue('statProses',     parseInt(document.getElementById('statProses').textContent),     data.total_proses,  400);
        animateValue('statSelesai',    parseInt(document.getElementById('statSelesai').textContent),    data.total_selesai, 400);

        // Pendapatan — langsung ganti karena format Rp
        const pendEl = document.getElementById('statPendapatan');
        pendEl.style.transition = 'opacity 0.2s';
        pendEl.style.opacity = '0';
        setTimeout(() => {
            pendEl.textContent = data.total_pendapatan_formatted;
            pendEl.style.opacity = '1';
        }, 200);

        // Update badge di navbar
        const badge = document.getElementById('badge-proses');
        if (badge) {
            if (data.total_proses > 0) {
                badge.textContent = data.total_proses;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    } catch(e) {
        // silent fail
    }
}

// Animasi angka naik/turun
function animateValue(elId, from, to, duration) {
    const el = document.getElementById(elId);
    if (!el || from === to) return;
    const start = performance.now();
    function step(now) {
        const progress = Math.min((now - start) / duration, 1);
        const ease = 1 - Math.pow(1 - progress, 3); // ease-out cubic
        el.textContent = Math.round(from + (to - from) * ease);
        if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
}

// SIMPAN PESANAN + UPDATE STATS
function submitPesanan() {
    const nama     = document.getElementById('nama_customer').value.trim();
    const jumlah   = document.getElementById('jumlah_helm').value;
    const layananId = document.getElementById('selected_layanan_id').value;

    if (!nama)      { showToast('Nama customer wajib diisi!', 'error'); return; }
    if (!jumlah || jumlah < 1) { showToast('Jumlah helm minimal 1!', 'error'); return; }
    if (!layananId) { showToast('Pilih layanan terlebih dahulu!', 'error'); return; }

    const selectedCard  = document.querySelector('.service-card-select.selected');
    const layananName   = selectedCard?.dataset.name || '';
    const layananPrice  = selectedCard?.dataset.price || 0;
    const total         = parseInt(layananPrice) * parseInt(jumlah);

    showConfirm(
        'Konfirmasi Pesanan',
        `Simpan pesanan ${nama} — ${layananName} x${jumlah} helm = Rp ${parseInt(total).toLocaleString('id-ID')}?`,
        () => doSavePesanan(nama, jumlah, layananId, layananName, layananPrice)
    );
}

async function doSavePesanan(nama, jumlah, layananId, layananName, layananPrice) {
    const btn = document.getElementById('btnSimpan');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Menyimpan...';

    try {
        const res = await ajaxRequest(STORE_URL, 'POST', {
            nama_customer : nama,
            jumlah_helm   : jumlah,
            layanan_id    : layananId,
        });

        if (res.success) {
            showToast('Pesanan berhasil disimpan!', 'success');

            // 1. Hapus baris kosong jika ada
            const emptyRow = document.getElementById('emptyRow');
            if (emptyRow) emptyRow.remove();

            // 2. Tambah baris baru ke ATAS tabel (tanpa reload)
            const tbody   = document.getElementById('pesananTbody');
            const rowCount = tbody.querySelectorAll('tr[data-id]').length;
            const newId    = res.pesanan.id;
            const total    = parseInt(layananPrice) * parseInt(jumlah);
            const now      = new Date();
            const jam      = String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');

            const tr = document.createElement('tr');
            tr.dataset.id = newId;
            tr.style.animation = 'slideDown 0.3s ease';
            tr.innerHTML = `
                <td style="color:var(--text-muted)">${rowCount + 1}</td>
                <td>
                    <span style="font-weight:600">${escapeHtml(nama)}</span>
                    <br><span style="font-size:11px;color:var(--text-muted)">${jam}</span>
                </td>
                <td>${escapeHtml(layananName)}</td>
                <td>${jumlah} helm</td>
                <td style="font-weight:700;color:var(--primary);font-family:var(--font-display)">
                    Rp ${total.toLocaleString('id-ID')}
                </td>
                <td><span class="badge badge-warning">Proses</span></td>
                <td>
                    <button class="btn btn-xs btn-success" onclick="toggleStatus(${newId}, this)">Selesai</button>
                </td>`;

            // Sisipkan di paling atas
            tbody.insertBefore(tr, tbody.firstChild);

            // Renumber semua baris
            renumberRows();

            // 3. Update 4 stat card langsung via AJAX
            await refreshStats();

            // 4. Reset form
            resetForm();
        } else {
            showToast(res.message || 'Terjadi kesalahan!', 'error');
        }
    } catch(e) {
        showToast('Gagal terhubung ke server!', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
            <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
        </svg> Simpan Pesanan`;
    }
}

function resetForm() {
    document.getElementById('nama_customer').value = '';
    document.getElementById('jumlah_helm').value   = '1';
    document.getElementById('selected_layanan_id').value = '';
    document.getElementById('selected_price_display').textContent = '';
    document.querySelectorAll('.service-card-select').forEach(c => c.classList.remove('selected'));
}

// TOGGLE STATUS + UPDATE STATS
async function toggleStatus(id, btn) {
    const url = `${STATUS_URL_BASE}/${id}/status`;
    try {
        const res = await fetch(url, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN'    : document.querySelector('meta[name="csrf-token"]').content,
                'Accept'          : 'application/json',
                'Content-Type'    : 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
        const data = await res.json();
        if (data.success) {
            const row       = btn.closest('tr');
            const badgeCell = row.querySelector('td:nth-child(6)');
            const isSelesai = data.status === 'selesai';

            badgeCell.innerHTML = `<span class="badge ${isSelesai ? 'badge-success' : 'badge-warning'}">${isSelesai ? 'Selesai' : 'Proses'}</span>`;
            btn.textContent  = isSelesai ? 'Proses' : 'Selesai';
            btn.className    = `btn btn-xs ${isSelesai ? 'btn-ghost' : 'btn-success'}`;

            showToast(`Status diubah ke ${data.status}!`, 'success');

            // Update stats setelah toggle
            await refreshStats();
        }
    } catch(e) {
        showToast('Gagal memperbarui status!', 'error');
    }
}

// FILTER
function applyFilter() {
    const nama    = document.getElementById('filterNama').value;
    const layanan = document.getElementById('filterLayanan').value;
    const params  = new URLSearchParams();
    if (nama)    params.set('filter_nama', nama);
    if (layanan) params.set('filter_layanan', layanan);
    window.location.href = '/' + (params.toString() ? '?' + params.toString() : '');
}

function resetFilter() {
    document.getElementById('filterNama').value    = '';
    document.getElementById('filterLayanan').value = '';
    window.location.href = '/';
}

document.getElementById('filterNama')?.addEventListener('keydown', e => { if(e.key === 'Enter') applyFilter(); });

// HELPERS
function renumberRows() {
    document.querySelectorAll('#pesananTbody tr[data-id]').forEach((row, i) => {
        const numCell = row.querySelector('td:first-child');
        if (numCell) numCell.textContent = i + 1;
    });
}

function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
</script>
@endpush
@endsection
