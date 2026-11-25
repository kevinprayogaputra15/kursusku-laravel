@extends('layouts.app')

@section('title', 'Daftar Kursus')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Daftar Kursus</h1>
            <p class="text-muted mb-0">Cari, filter status, dan telusuri per halaman (10 data).</p>
        </div>
        <a href="{{ route('courses.create') }}" class="btn btn-primary">
            + Tambah Kursus
        </a>
    </div>

    {{-- Flash message success sudah ditampilkan oleh layouts/app.blade.php --}}

    {{-- Form GET = baseline yang tetap bekerja tanpa JavaScript. Tidak butuh @csrf. --}}
    <form id="course-filter" method="GET" action="{{ route('courses.index') }}" class="row g-2 mb-3">
        <div class="col-md-6">
            <input type="search" id="live-search" name="q" value="{{ $search }}"
                   class="form-control" placeholder="Cari kode atau nama kursus"
                   autocomplete="off">
        </div>
        <div class="col-md-3">
            <select id="status-filter" name="status" class="form-select">
                <option value="">Semua status</option>
                <option value="open" @selected($status === 'open')>Open</option>
                <option value="closed" @selected($status === 'closed')>Closed</option>
                <option value="draft" @selected($status === 'draft')>Draft</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div id="ajax-status" class="small text-muted mb-2" aria-live="polite"></div>

    @include('courses._results')
</div>
@endsection

@push('scripts')
{{-- Ajax = peningkatan UX (progressive enhancement). Tanpa JavaScript, form GET di atas tetap bekerja. --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // jQuery dimuat oleh resources/js/app.js (module) dan sudah siap saat DOMContentLoaded.
    if (!window.jQuery) { return; }
    const $ = window.jQuery;

    const indexUrl = @json(route('courses.index'));
    let searchTimer;
    let lastRequest;

    function setStatus(text) { $('#ajax-status').text(text); }

    function load(url) {
        const u = new URL(url, window.location.origin);
        u.searchParams.set('partial', '1');

        $('#course-results').css('opacity', 0.5);
        setStatus('Memuat hasil...');

        if (lastRequest) { lastRequest.abort(); }

        lastRequest = $.get(u.toString())
            .done(function (html) {
                $('#course-results').replaceWith(html);
                setStatus('');

                // Perbarui URL (tanpa partial) agar tetap bisa disalin/di-bookmark.
                u.searchParams.delete('partial');
                window.history.replaceState(null, '', u.pathname + u.search);
            })
            .fail(function (xhr, status) {
                if (status === 'abort') { return; }
                $('#course-results').css('opacity', 1);
                setStatus('Gagal memuat hasil. Gunakan tombol Cari.');
                console.error('Gagal memuat hasil course.');
            });
    }

    function currentUrl() {
        const params = new URLSearchParams();
        const q = $('#live-search').val().trim();
        const status = $('#status-filter').val();
        if (q !== '') { params.set('q', q); }
        if (status !== '') { params.set('status', status); }
        const qs = params.toString();
        return indexUrl + (qs ? '?' + qs : '');
    }

    // Debounce 300 ms: request dikirim setelah pengguna berhenti mengetik.
    $(document).on('input', '#live-search', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { load(currentUrl()); }, 300);
    });

    $(document).on('change', '#status-filter', function () {
        load(currentUrl());
    });

    // Ajax pagination: link pagination membawa q/status (withQueryString).
    $(document).on('click', '#course-results .pagination a', function (e) {
        e.preventDefault();
        load($(this).attr('href'));
    });
});
</script>
@endpush
