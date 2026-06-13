@extends('layouts.app')
@php $pageTitle = 'Layanan'; @endphp
@section('content')

<div class="page-header flex justify-between items-center flex-wrap" style="gap:16px">
    <div>
        <h1 class="page-title">Manajemen <span>Layanan</span></h1>
        <p class="page-desc">Kelola layanan cuci helm yang tersedia</p>
    </div>
    <button class="btn btn-primary" onclick="openAddModal()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Tambah Layanan
    </button>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; align-items:start;">
    <!-- Layanan Grid -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Daftar Layanan</div>
                <div class="card-subtitle">{{ $layanan->count() }} layanan ({{ $layanan->where('is_active', true)->count() }} aktif)</div>
            </div>
        </div>
        <div class="layanan-grid" id="layananGrid">
            @forelse($layanan as $l)
                <div class="layanan-item {{ !$l->is_active ? 'layanan-inactive' : '' }}" id="layanan-item-{{ $l->id }}">
                    <div class="layanan-info">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div class="layanan-name" style="{{ !$l->is_active ? 'color:var(--text-muted);' : '' }}">{{ $l->nama_layanan }}</div>
                            <span class="badge {{ $l->is_active ? 'badge-success' : 'badge-warning' }}" style="font-size:10px;padding:2px 7px;">
                                {{ $l->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        <div class="layanan-price" style="{{ !$l->is_active ? 'color:var(--text-muted);' : '' }}">Rp {{ number_format($l->harga, 0, ',', '.') }}</div>
                        @if($l->deskripsi)
                            <div style="font-size:11px;color:var(--text-muted);margin-top:3px">{{ $l->deskripsi }}</div>
                        @endif
                    </div>
                    <div class="layanan-actions">
                        <button class="btn btn-icon btn-sm {{ $l->is_active ? 'btn-ghost' : 'btn-primary' }}" title="{{ $l->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                onclick="toggleLayanan({{ $l->id }}, '{{ addslashes($l->nama_layanan) }}', {{ $l->is_active ? 'true' : 'false' }})">
                            @if($l->is_active)
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><rect x="1" y="5" width="22" height="14" rx="7" ry="7"/><circle cx="16" cy="12" r="3" fill="currentColor"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><rect x="1" y="5" width="22" height="14" rx="7" ry="7"/><circle cx="8" cy="12" r="3" fill="currentColor"/></svg>
                            @endif
                        </button>
                        <button class="btn btn-icon btn-ghost btn-sm" title="Edit"
                                onclick="openEditModal({{ $l->id }}, '{{ addslashes($l->nama_layanan) }}', {{ $l->harga }}, '{{ addslashes($l->deskripsi ?? '') }}')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </button>
                        <button class="btn btn-icon btn-danger btn-sm" title="Hapus"
                                onclick="deleteLayanan({{ $l->id }}, '{{ addslashes($l->nama_layanan) }}')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="empty-state" style="grid-column:1/-1">
                    <div class="empty-icon">📋</div>
                    <div class="empty-text">Belum ada layanan. Tambah layanan baru.</div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Info Card -->
    <div>
        <div class="card mb-4">
            <div class="card-header"><div class="card-title">Statistik Layanan</div></div>
            <div class="card-body">
                @foreach($layanan as $l)
                    @php $countPesanan = $l->pesanan()->count(); @endphp
                    <div id="statistik-item-{{ $l->id }}" style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border);last-child:{border:none}">
                        <div>
                            <div style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ $l->nama_layanan }}</div>
                            <div style="font-size:11px;color:var(--text-muted)">{{ $countPesanan }} total pesanan</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:13px;font-weight:700;color:var(--primary);font-family:var(--font-display)">
                                Rp {{ number_format($l->harga, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card" style="background:linear-gradient(135deg,rgba(255,107,43,0.1),rgba(255,107,43,0.03));border-color:rgba(255,107,43,0.2);">
            <div class="card-body" style="text-align:center;padding:28px">
                <div style="font-family:var(--font-display);font-size:16px;font-weight:700;color:var(--text-primary);margin-bottom:6px;">Informasi Layanan</div>
                <p style="font-size:13px;color:var(--text-secondary);line-height:1.6">
                    Layanan yang ditambahkan di sini akan otomatis muncul sebagai kartu pilihan di halaman Dashboard saat input pesanan.
                </p>
            </div>
        </div>
    </div>
</div>

<footer>
    <div class="footer-brand">Cuci<span>Helm</span> Pro</div>
    <div class="footer-copy">© {{ date('Y') }} Sistem Kasir Jasa Cuci Helm</div>
    <div class="footer-tag">Dibuat dengan <span>tujuan</span> untuk kemudahan kasir</div>
</footer>

<!-- Modal Tambah/Edit Layanan -->
<div class="modal-backdrop" id="layananModal">
    <div class="modal-box" style="max-width:440px;text-align:left;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <h3 class="modal-title" id="layananModalTitle" style="margin:0;">Tambah Layanan</h3>
            <button onclick="closeLayananModal()" style="color:var(--text-muted);width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;transition:all 0.2s;" onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background='none'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <input type="hidden" id="editLayananId">
        <div class="form-group">
            <label class="form-label">Nama Layanan</label>
            <input type="text" class="form-control" id="inputNamaLayanan" placeholder="cth: Cuci Premium">
        </div>
        <div class="form-group">
            <label class="form-label">Harga (Rp)</label>
            <input type="number" class="form-control" id="inputHarga" placeholder="cth: 25000" min="1000">
        </div>
        <div class="form-group" style="margin-bottom:20px">
            <label class="form-label">Deskripsi (opsional)</label>
            <input type="text" class="form-control" id="inputDeskripsi" placeholder="Keterangan singkat layanan...">
        </div>

        <div style="display:flex;gap:10px;">
            <button class="btn btn-ghost" style="flex:1" onclick="closeLayananModal()">Batal</button>
            <button class="btn btn-primary" style="flex:2" id="btnSaveLayanan" onclick="saveLayanan()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Simpan
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const LAYANAN_BASE = "{{ url('/layanan') }}";

function openAddModal() {
    document.getElementById('editLayananId').value = '';
    document.getElementById('inputNamaLayanan').value = '';
    document.getElementById('inputHarga').value = '';
    document.getElementById('inputDeskripsi').value = '';
    document.getElementById('layananModalTitle').textContent = 'Tambah Layanan';
    document.getElementById('layananModal').classList.add('active');
}

function openEditModal(id, nama, harga, deskripsi) {
    document.getElementById('editLayananId').value = id;
    document.getElementById('inputNamaLayanan').value = nama;
    document.getElementById('inputHarga').value = harga;
    document.getElementById('inputDeskripsi').value = deskripsi;
    document.getElementById('layananModalTitle').textContent = 'Edit Layanan';
    document.getElementById('layananModal').classList.add('active');
}

function closeLayananModal() {
    document.getElementById('layananModal').classList.remove('active');
}

async function saveLayanan() {
    const id    = document.getElementById('editLayananId').value;
    const nama  = document.getElementById('inputNamaLayanan').value.trim();
    const harga = document.getElementById('inputHarga').value;
    const desk  = document.getElementById('inputDeskripsi').value.trim();

    if (!nama) { showToast('Nama layanan wajib diisi!', 'error'); return; }
    if (!harga || harga < 1000) { showToast('Harga minimal Rp 1.000!', 'error'); return; }

    const btn = document.getElementById('btnSaveLayanan');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Menyimpan...';

    const isEdit = !!id;
    const url    = isEdit ? `${LAYANAN_BASE}/${id}` : LAYANAN_BASE;
    const method = isEdit ? 'PUT' : 'POST';

    try {
        const res = await ajaxRequest(url, method, {
            nama_layanan: nama,
            harga: harga,
            deskripsi: desk,
        });
        if (res.success) {
            showToast(res.message, 'success');
            closeLayananModal();
            setTimeout(() => location.reload(), 600);
        } else {
            const errMsg = res.errors ? Object.values(res.errors).flat().join(', ') : (res.message || 'Terjadi kesalahan!');
            showToast(errMsg, 'error');
        }
    } catch(e) {
        showToast('Gagal terhubung ke server!', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Simpan';
    }
}

function deleteLayanan(id, nama) {
    showConfirm(
        'Hapus Layanan',
        `Yakin ingin menghapus layanan "${nama}"? Tindakan ini tidak dapat dibatalkan.`,
        async () => {
            try {
                const res = await ajaxRequest(`${LAYANAN_BASE}/${id}`, 'DELETE');
                if (res.success) {
                    showToast(res.message, 'success');
                    const el = document.getElementById(`layanan-item-${id}`);
                    if (el) {
                        el.style.transition = 'opacity 0.3s';
                        el.style.opacity = '0';
                        setTimeout(() => { el.remove(); }, 300);
                    }
                    const statEl = document.getElementById(`statistik-item-${id}`);
                    if (statEl) {
                        statEl.style.transition = 'opacity 0.3s';
                        statEl.style.opacity = '0';
                        setTimeout(() => { statEl.remove(); }, 300);
                    }
                } else {
                    showToast(res.message, 'error');
                }
            } catch(e) {
                showToast('Gagal menghapus layanan!', 'error');
            }
        },
        'btn-danger'
    );
}

function toggleLayanan(id, nama, isCurrentlyActive) {
    const action = isCurrentlyActive ? 'nonaktifkan' : 'aktifkan';
    const confirmMsg = isCurrentlyActive
        ? `Nonaktifkan layanan "${nama}"? Layanan tidak akan muncul di dashboard.`
        : `Aktifkan kembali layanan "${nama}"? Layanan akan muncul di dashboard.`;

    showConfirm(
        isCurrentlyActive ? 'Nonaktifkan Layanan' : 'Aktifkan Layanan',
        confirmMsg,
        async () => {
            try {
                const res = await ajaxRequest(`${LAYANAN_BASE}/${id}/toggle`, 'PATCH');
                if (res.success) {
                    showToast(res.message, 'success');
                    setTimeout(() => location.reload(), 600);
                } else {
                    showToast(res.message || 'Terjadi kesalahan!', 'error');
                }
            } catch(e) {
                showToast('Gagal terhubung ke server!', 'error');
            }
        },
        'btn-primary'
    );
}

// Tutup modal saat backdrop click
document.getElementById('layananModal').addEventListener('click', e => {
    if (e.target === document.getElementById('layananModal')) closeLayananModal();
});
</script>
@endpush
@endsection
