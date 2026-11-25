<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pertemuan 13: search q, filter status, pagination 10/page, empty state, partial.
 * Jalankan: php artisan test --filter=CourseSearchTest
 */
class CourseSearchTest extends TestCase
{
    use RefreshDatabase;

    private function makeCourse(string $code, string $name, string $status = 'open'): Course
    {
        return Course::create([
            'code' => $code,
            'name' => $name,
            'description' => 'Data uji.',
            'fee' => 300000,
            'quota' => 20,
            'start_date' => '2026-12-01',
            'status' => $status,
        ]);
    }

    /** Buat N course "Kursus Massal 01..N" dengan kode MASS-001.. */
    private function makeMany(int $count, string $status = 'open'): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $this->makeCourse(
                sprintf('MASS-%s-%03d', strtoupper(substr($status, 0, 1)), $i),
                'Kursus Massal '.sprintf('%02d', $i),
                $status
            );
        }
    }

    // ---- 5 skenario utama panduan ----

    public function test_01_nol_hasil_menampilkan_empty_state_tanpa_error(): void
    {
        $this->makeMany(3);

        $this->get(route('courses.index', ['q' => 'tidak-ada-xyz-999']))
            ->assertOk()
            ->assertSee('Tidak ada kursus yang sesuai pencarian/filter.')
            ->assertSee('0 kursus ditemukan');
    }

    public function test_02_satu_hasil_tepat_satu_course(): void
    {
        $this->makeMany(5);
        $this->makeCourse('UNIK-001', 'Kursus Unik Zeta');

        $response = $this->get(route('courses.index', ['q' => 'Unik Zeta']))->assertOk();

        $response->assertSee('Kursus Unik Zeta')
            ->assertDontSee('Kursus Massal 01')
            ->assertSee('dari 1 kursus');
    }

    public function test_03_lebih_dari_sepuluh_hasil_dipaginasi_10_per_halaman(): void
    {
        $this->makeMany(12);

        $response = $this->get(route('courses.index'))->assertOk();

        $this->assertCount(10, $response->viewData('courses')->items());
        $this->assertSame(12, $response->viewData('courses')->total());
        $response->assertSee('page=2', false);
    }

    public function test_04_keyword_dengan_spasi_terbaca_benar(): void
    {
        $this->makeCourse('WEB-A', 'Web Modern Pemula');
        $this->makeCourse('WEB-B', 'Web Klasik');

        $this->get(route('courses.index', ['q' => 'Web Modern']))
            ->assertOk()
            ->assertSee('Web Modern Pemula')
            ->assertDontSee('Web Klasik');

        // spasi di awal/akhir dibuang (trim)
        $this->get(route('courses.index', ['q' => '  Web Modern  ']))
            ->assertSee('Web Modern Pemula')
            ->assertDontSee('Web Klasik');
    }

    public function test_05_filter_status_dan_page_2_mempertahankan_query_string(): void
    {
        $this->makeMany(12, 'open');
        $this->makeMany(3, 'closed');

        $response = $this->get(route('courses.index', ['status' => 'open', 'q' => 'Massal', 'page' => 2]))
            ->assertOk();

        $paginator = $response->viewData('courses');
        $this->assertSame(12, $paginator->total());
        $this->assertCount(2, $paginator->items());
        $this->assertSame(2, $paginator->currentPage());
        $this->assertStringContainsString('status=open', $paginator->url(1));
        $this->assertStringContainsString('q=Massal', $paginator->url(1));
        $response->assertSee('status=open', false);
    }

    // ---- Tambahan ----

    public function test_cari_berdasarkan_kode(): void
    {
        $this->makeCourse('ZZZ-777', 'Nama Biasa');
        $this->makeCourse('AAA-111', 'Nama Lain');

        $this->get(route('courses.index', ['q' => 'ZZZ']))
            ->assertSee('Nama Biasa')
            ->assertDontSee('Nama Lain');
    }

    public function test_filter_status_closed_dan_draft(): void
    {
        $this->makeCourse('O-1', 'Kursus Terbuka', 'open');
        $this->makeCourse('C-1', 'Kursus Tertutup', 'closed');
        $this->makeCourse('D-1', 'Kursus Konsep', 'draft');

        $this->get(route('courses.index', ['status' => 'closed']))
            ->assertSee('Kursus Tertutup')->assertDontSee('Kursus Terbuka')->assertDontSee('Kursus Konsep');

        $this->get(route('courses.index', ['status' => 'draft']))
            ->assertSee('Kursus Konsep')->assertDontSee('Kursus Terbuka')->assertDontSee('Kursus Tertutup');
    }

    public function test_status_kosong_atau_tidak_valid_tidak_memfilter(): void
    {
        $this->makeCourse('O-1', 'Kursus Terbuka', 'open');
        $this->makeCourse('C-1', 'Kursus Tertutup', 'closed');

        foreach (['', 'active', 'OPEN; DROP TABLE courses'] as $status) {
            $this->get(route('courses.index', ['status' => $status]))
                ->assertOk()
                ->assertSee('Kursus Terbuka')
                ->assertSee('Kursus Tertutup');
        }
    }

    public function test_search_dan_filter_digabung(): void
    {
        $this->makeCourse('W-1', 'Web Dasar', 'open');
        $this->makeCourse('W-2', 'Web Lanjut', 'closed');

        $this->get(route('courses.index', ['q' => 'Web', 'status' => 'open']))
            ->assertSee('Web Dasar')
            ->assertDontSee('Web Lanjut');
    }

    public function test_form_get_dan_nilai_input_dipertahankan(): void
    {
        $this->makeCourse('W-1', 'Web Dasar', 'closed');

        $this->get(route('courses.index', ['q' => 'Web', 'status' => 'closed']))
            ->assertSee('method="GET"', false)
            ->assertSee('value="Web"', false)
            ->assertSee('<option value="closed" selected>', false);
    }

    // ---- Partial / Ajax ----

    public function test_partial_hanya_mengembalikan_blok_hasil(): void
    {
        $this->makeCourse('W-1', 'Web Dasar');

        $this->get(route('courses.index', ['q' => 'Web', 'partial' => 1]))
            ->assertOk()
            ->assertSee('id="course-results"', false)
            ->assertSee('Web Dasar')
            ->assertDontSee('<html', false)
            ->assertDontSee('id="live-search"', false);
    }

    public function test_halaman_penuh_memuat_layout_dan_form(): void
    {
        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee('<html', false)
            ->assertSee('id="live-search"', false)
            ->assertSee('id="course-results"', false);
    }

    public function test_link_pagination_pada_partial_tidak_membawa_parameter_partial(): void
    {
        $this->makeMany(12);

        $response = $this->get(route('courses.index', ['partial' => 1]))->assertOk();

        $this->assertStringNotContainsString('partial=1', $response->getContent());
        $response->assertSee('page=2', false);
    }

    // ---- Dashboard count ----

    public function test_beranda_menampilkan_hitungan_dashboard(): void
    {
        $open = $this->makeCourse('O-1', 'Kursus Terbuka', 'open');
        $this->makeCourse('C-1', 'Kursus Tertutup', 'closed');
        $p = Participant::create(['name' => 'Alya', 'email' => 'alya@example.test']);
        Enrollment::create(['participant_id' => $p->id, 'course_id' => $open->id, 'status' => 'registered']);

        $response = $this->get(route('home'))->assertOk();

        $this->assertSame(
            ['courses' => 2, 'open_courses' => 1, 'participants' => 1, 'enrollments' => 1],
            $response->viewData('stats')
        );
        $response->assertSee('Ringkasan Data');
    }
}
