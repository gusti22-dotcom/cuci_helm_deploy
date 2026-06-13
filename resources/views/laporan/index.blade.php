@extends('layouts.app')
@php $pageTitle = 'Laporan'; @endphp
@section('content')

<div class="page-header">
    <h1 class="page-title">Laporan <span>Pendapatan</span></h1>
    <p class="page-desc">Pantau total pendapatan harian dan bulanan</p>
</div>

<!-- Summary Stats -->
<div class="stat-grid" style="margin-bottom:24px">
    <div class="stat-card green">
        <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="stat-value" style="font-size:18px" id="statTotalPendapatan">Rp {{ number_format($totalBulanIni, 0, ',', '.') }}</div>
        <div class="stat-label">Total Pendapatan Bulan Ini</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg></div>
        <div class="stat-value" id="statTotalPesanan">{{ $totalPesanan }}</div>
        <div class="stat-label">Total Pesanan Bulan Ini</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></div>
        <div class="stat-value" style="font-size:18px" id="statRataHari">
            Rp {{ $laporanHarian->count() > 0 ? number_format($totalBulanIni / $laporanHarian->count(), 0, ',', '.') : '0' }}
        </div>
        <div class="stat-label">Rata-rata Per Hari</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
        <div class="stat-value" id="statHariAktif">{{ $laporanHarian->count() }}</div>
        <div class="stat-label">Hari Aktif Bulan Ini</div>
    </div>
</div>

<!-- Filter + Chart -->
<div class="card mb-4">
    <div class="card-header">
        <div>
            <div class="card-title">Grafik Pendapatan Harian</div>
            <div class="card-subtitle" id="chartSubtitle">
                @php
                    $namaBulan = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
                                  '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
                @endphp
                {{ $namaBulan[$filterBulan] ?? '' }} {{ $filterTahun }}
            </div>
        </div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <select class="form-control" id="laporanBulan" style="width:140px">
                @foreach($namaBulan as $val => $name)
                    <option value="{{ $val }}" {{ $filterBulan == $val ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            <select class="form-control" id="laporanTahun" style="width:110px">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $filterTahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary btn-sm" onclick="loadLaporan()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Tampilkan
            </button>
        </div>
    </div>
    <div class="chart-container" id="chartContainer">
        @if($laporanHarian->isNotEmpty())
            <div class="chart-bars" id="chartBars">
                @foreach($laporanHarian as $item)
                    @php $pct = $maxPendapatan > 0 ? ($item->pendapatan / $maxPendapatan * 100) : 0; @endphp
                    <div class="chart-bar-wrap" title="Rp {{ number_format($item->pendapatan,0,',','.') }}">
                        <div class="chart-bar" data-percent="{{ number_format($pct,1) }}" style="height:{{ number_format($pct,1) }}%"></div>
                        <span class="chart-bar-label">{{ \Carbon\Carbon::parse($item->tanggal)->format('d') }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state" id="chartEmpty">
                <div class="empty-text">Tidak ada data untuk periode ini</div>
            </div>
        @endif
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <!-- Tabel Harian -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Rincian Per Hari</div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pesanan</th>
                        <th>Selesai</th>
                        <th>Pendapatan</th>
                    </tr>
                </thead>
                <tbody id="tbodyHarian">
                    @forelse($laporanHarian as $item)
                        <tr>
                            <td style="font-weight:600">{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</td>
                            <td>{{ $item->total_pesanan }}</td>
                            <td><span class="badge badge-success">{{ $item->selesai }}</span></td>
                            <td style="font-weight:700;color:var(--primary);font-family:var(--font-display)">
                                Rp {{ number_format($item->pendapatan, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyHarian"><td colspan="4"><div class="empty-state"><div class="empty-text">Tidak ada data</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Per Layanan -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Pendapatan Per Layanan</div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Layanan</th>
                        <th>Pesanan</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody id="tbodyLayanan">
                    @forelse($laporanLayanan as $item)
                        <tr>
                            <td style="font-weight:600">{{ $item->nama_layanan }}</td>
                            <td>{{ $item->total_pesanan }}</td>
                            <td style="font-weight:700;color:var(--primary);font-family:var(--font-display)">
                                Rp {{ number_format($item->total_pendapatan, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyLayanan"><td colspan="3"><div class="empty-state"><div class="empty-text">Tidak ada data</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
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
const API_LAPORAN = "{{ route('api.laporan') }}";
const NAMA_BULAN  = {
    '01':'Januari','02':'Februari','03':'Maret','04':'April',
    '05':'Mei','06':'Juni','07':'Juli','08':'Agustus',
    '09':'September','10':'Oktober','11':'November','12':'Desember'
};

// Fungsi utama: load semua data laporan via AJAX
async function loadLaporan() {
    const bulan = document.getElementById('laporanBulan').value;
    const tahun = document.getElementById('laporanTahun').value;

    // Tampilkan loading di semua area
    setLoadingState(true);

    try {
        const res  = await fetch(`${API_LAPORAN}?bulan=${bulan}&tahun=${tahun}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        // 1. Update subtitle
        document.getElementById('chartSubtitle').textContent =
            `${NAMA_BULAN[bulan] || bulan} ${tahun}`;

        // 2. Update 4 stat cards
        updateStatCards(data.stats);

        // 3. Update chart
        updateChart(data.harian);

        // 4. Update tabel harian
        updateTabelHarian(data.harian);

        // 5. Update tabel per layanan
        updateTabelLayanan(data.layanan);

    } catch(e) {
        showToast('Gagal memuat data laporan!', 'error');
    } finally {
        setLoadingState(false);
    }
}

// Update 4 stat cards
function updateStatCards(stats) {
    // Total pendapatan
    const elPend = document.getElementById('statTotalPendapatan');
    elPend.style.opacity = '0';
    setTimeout(() => {
        elPend.textContent = stats.total_pendapatan_fmt;
        elPend.style.transition = 'opacity 0.3s';
        elPend.style.opacity    = '1';
    }, 150);

    // Total pesanan (animasi angka)
    animateNumber('statTotalPesanan', stats.total_pesanan);

    // Rata-rata per hari
    const elRata = document.getElementById('statRataHari');
    elRata.style.opacity = '0';
    setTimeout(() => {
        elRata.textContent = stats.rata_hari_fmt;
        elRata.style.transition = 'opacity 0.3s';
        elRata.style.opacity    = '1';
    }, 150);

    // Hari aktif (animasi angka)
    animateNumber('statHariAktif', stats.hari_aktif);
}

// Animasi angka naik/turun
function animateNumber(elId, targetVal) {
    const el   = document.getElementById(elId);
    if (!el) return;
    const from = parseInt(el.textContent.replace(/\D/g, '')) || 0;
    const to   = parseInt(targetVal) || 0;
    if (from === to) return;

    const dur   = 400;
    const start = performance.now();
    (function step(now) {
        const p  = Math.min((now - start) / dur, 1);
        const e  = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(from + (to - from) * e);
        if (p < 1) requestAnimationFrame(step);
    })(performance.now());
}

//  Update chart bars
function updateChart(harian) {
    const container = document.getElementById('chartContainer');

    if (!harian.length) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-text">Tidak ada data untuk periode ini</div>
            </div>`;
        return;
    }

    container.innerHTML = `<div class="chart-bars" id="chartBars">
        ${harian.map(item => `
            <div class="chart-bar-wrap" title="${item.pendapatan_fmt}">
                <div class="chart-bar" data-percent="${item.persen}" style="height:0%"></div>
                <span class="chart-bar-label">${item.tanggal}</span>
            </div>`).join('')}
    </div>`;

    // Animate bars setelah render
    requestAnimationFrame(() => {
        setTimeout(() => {
            container.querySelectorAll('.chart-bar').forEach(bar => {
                bar.style.height = bar.dataset.percent + '%';
            });
        }, 50);
    });
}

// Update tabel harian
function updateTabelHarian(harian) {
    const tbody = document.getElementById('tbodyHarian');
    if (!harian.length) {
        tbody.innerHTML = `<tr><td colspan="4">
            <div class="empty-state">
            <div class="empty-text">Tidak ada data</div></div></td></tr>`;
        return;
    }

    tbody.innerHTML = harian.map(item => `
        <tr>
            <td style="font-weight:600">${item.tanggal_label}</td>
            <td>${item.total_pesanan}</td>
            <td><span class="badge badge-success">${item.selesai}</span></td>
            <td style="font-weight:700;color:var(--primary);font-family:var(--font-display)">
                ${item.pendapatan_fmt}
            </td>
        </tr>`).join('');
}

// Update tabel per layanan
function updateTabelLayanan(layanan) {
    const tbody = document.getElementById('tbodyLayanan');
    if (!layanan.length) {
        tbody.innerHTML = `<tr><td colspan="3">
            <div class="empty-state">
            <div class="empty-text">Tidak ada data</div></div></td></tr>`;
        return;
    }

    tbody.innerHTML = layanan.map(item => `
        <tr>
            <td style="font-weight:600">${escapeHtml(item.nama_layanan)}</td>
            <td>${item.total_pesanan}</td>
            <td style="font-weight:700;color:var(--primary);font-family:var(--font-display)">
                ${item.total_pendapatan_fmt}
            </td>
        </tr>`).join('');
}

// Loading state
function setLoadingState(isLoading) {
    if (isLoading) {
        document.getElementById('chartContainer').innerHTML =
            `<div style="text-align:center;padding:48px"><span class="spinner"></span></div>`;
        ['tbodyHarian','tbodyLayanan'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerHTML = `<tr><td colspan="4" style="text-align:center;padding:24px"><span class="spinner"></span></td></tr>`;
        });
    }
}

// Helper
function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
</script>
@endpush
@endsection
