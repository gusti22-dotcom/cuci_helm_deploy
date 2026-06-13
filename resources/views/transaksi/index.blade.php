@extends('layouts.app')
@php $pageTitle = 'Transaksi'; @endphp
@section('content')

<div class="page-header">
    <h1 class="page-title">Riwayat <span>Transaksi</span></h1>
    <p class="page-desc">Pantau semua riwayat transaksi secara lengkap</p>
</div>

<!-- Stats -->
<div class="stat-grid" style="grid-template-columns:repeat(2,1fr);max-width:500px;margin-bottom:24px">
    <div class="stat-card green">
        <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="stat-value" style="font-size:18px" id="statPendapatan">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
        <div class="stat-label">Total Pendapatan</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></div>
        <div class="stat-value" id="statTotal">{{ $totalPesanan }}</div>
        <div class="stat-label">Total Pesanan</div>
    </div>
</div>

<!-- Card dengan filter + table -->
<div class="card">
    <div class="filter-bar">
        <div class="filter-group" style="min-width:110px;max-width:140px">
            <span class="filter-label">Tanggal</span>
            <select class="form-control" id="filterTanggal">
                <option value="">-</option>
                @for($d = 1; $d <= 31; $d++)
                    <option value="{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}"
                        {{ $filterTanggal == str_pad($d, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                        {{ $d }}
                    </option>
                @endfor
            </select>
        </div>
        <div class="filter-group" style="min-width:130px;max-width:170px">
            <span class="filter-label">Bulan</span>
            <select class="form-control" id="filterBulan">
                <option value="">Semua Bulan</option>
                @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $val => $name)
                    <option value="{{ $val }}" {{ $filterBulan == $val ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group" style="min-width:110px;max-width:140px">
            <span class="filter-label">Tahun</span>
            <select class="form-control" id="filterTahun">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $filterTahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-actions">
            <button class="btn btn-primary" onclick="applyFilter()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Filter
            </button>
            <button class="btn btn-ghost" onclick="resetFilter()">Reset</button>
        </div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th style="width:44px">No</th>
                    <th>Nama Customer</th>
                    <th>Layanan</th>
                    <th>Jumlah</th>
                    <th>Total Harga</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody id="transaksiTbody">
                @forelse($transaksi as $i => $t)
                    <tr>
                        <td style="color:var(--text-muted)">{{ $i + 1 }}</td>
                        <td><span style="font-weight:600">{{ $t->nama_customer }}</span></td>
                        <td>{{ $t->nama_layanan }}</td>
                        <td>{{ $t->jumlah_helm }} helm</td>
                        <td style="font-weight:700;color:var(--primary);font-family:var(--font-display)">
                            Rp {{ number_format($t->total_harga, 0, ',', '.') }}
                        </td>
                        <td>
                            <span class="badge {{ $t->status === 'selesai' ? 'badge-success' : 'badge-warning' }}">
                                {{ ucfirst($t->status) }}
                            </span>
                        </td>
                        <td style="color:var(--text-secondary);font-size:13px">
                            {{ $t->tanggal->format('d/m/Y') }}
                            <br><span style="font-size:11px;color:var(--text-muted)">{{ $t->created_at->format('H:i') }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-text">Tidak ada transaksi ditemukan</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<footer>
    <div class="footer-brand">Cuci<span>Helm</span> Pro</div>
    <div class="footer-copy">© {{ date('Y') }} Sistem Kasir Jasa Cuci Helm</div>
    <div class="footer-tag">Dibuat dengan <span>tujuan</span> untuk kemudahan kasir</div>
</footer>

@push('scripts')
<script>
const API_TRANSAKSI = "{{ route('api.transaksi') }}";

function applyFilter() {
    const tgl    = document.getElementById('filterTanggal').value;
    const bulan  = document.getElementById('filterBulan').value;
    const tahun  = document.getElementById('filterTahun').value;

    // Gunakan AJAX untuk async update
    const params = new URLSearchParams();
    if (tgl) params.set('tanggal', tgl);
    if (bulan) params.set('bulan', bulan);
    if (tahun) params.set('tahun', tahun);

    fetchTransaksi(params);
}

function resetFilter() {
    document.getElementById('filterTanggal').value = '';
    document.getElementById('filterBulan').value = '';
    document.getElementById('filterTahun').value = new Date().getFullYear();
    fetchTransaksi(new URLSearchParams({ tahun: new Date().getFullYear() }));
}

async function fetchTransaksi(params) {
    const tbody = document.getElementById('transaksiTbody');
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:40px"><span class="spinner"></span></td></tr>`;

    try {
        const res = await fetch(`${API_TRANSAKSI}?${params.toString()}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        // Update stats
        document.getElementById('statPendapatan').textContent = data.total_pendapatan;
        document.getElementById('statTotal').textContent = data.total_pesanan;

        if (!data.data.length) {
            tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><div class="empty-text">Tidak ada transaksi ditemukan</div></div></td></tr>`;
            return;
        }

        tbody.innerHTML = data.data.map((t, i) => `
            <tr>
                <td style="color:var(--text-muted)">${i + 1}</td>
                <td><span style="font-weight:600">${escapeHtml(t.nama_customer)}</span></td>
                <td>${escapeHtml(t.nama_layanan)}</td>
                <td>${t.jumlah_helm} helm</td>
                <td style="font-weight:700;color:var(--primary);font-family:var(--font-display)">${t.total_formatted}</td>
                <td><span class="badge ${t.status === 'selesai' ? 'badge-success' : 'badge-warning'}">${capitalize(t.status)}</span></td>
                <td style="color:var(--text-secondary);font-size:13px">${t.tanggal}</td>
            </tr>
        `).join('');
    } catch(e) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:var(--danger);padding:20px">Gagal memuat data</td></tr>`;
    }
}

function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
function capitalize(str) { return str.charAt(0).toUpperCase() + str.slice(1); }
</script>
@endpush
@endsection
