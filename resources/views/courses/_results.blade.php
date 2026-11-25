{{-- Partial hasil Course: dipakai oleh halaman penuh DAN respons Ajax (?partial=1). --}}
<div id="course-results">
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Biaya</th>
                            <th>Kuota</th>
                            <th>Mulai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($courses as $course)
                        <tr>
                            <td>{{ $course->code }}</td>
                            <td>{{ $course->name }}</td>
                            <td>Rp {{ number_format($course->fee, 0, ',', '.') }}</td>
                            <td>{{ $course->quota }}</td>
                            <td>{{ $course->start_date->format('d/m/Y') }}</td>
                            <td><span class="kk-status kk-status-{{ $course->status }}">{{ ucfirst($course->status) }}</span></td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <a href="{{ route('courses.show', $course) }}"
                                       class="btn btn-sm btn-outline-primary">Detail</a>
                                    <a href="{{ route('courses.edit', $course) }}"
                                       class="btn btn-sm btn-outline-warning">Edit</a>
                                    <form method="POST"
                                          action="{{ route('courses.destroy', $course) }}"
                                          onsubmit="return confirm('Hapus kursus ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Tidak ada kursus yang sesuai pencarian/filter.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
        <div class="text-muted small">
            @if ($courses->total() > 0)
                Menampilkan {{ $courses->firstItem() }}-{{ $courses->lastItem() }}
                dari {{ $courses->total() }} kursus
            @else
                0 kursus ditemukan
            @endif
        </div>
        {{ $courses->links() }}
    </div>
</div>
