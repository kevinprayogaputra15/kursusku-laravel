<?php

namespace Tests\Feature;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test matrix Pertemuan 11 (15 kasus) untuk CRUD Course.
 * Jalankan: php artisan test --filter=CourseCrudTest
 */
class CourseCrudTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $override = []): array
    {
        return array_merge([
            'code' => 'NEW-001',
            'name' => 'Kursus Baru',
            'description' => 'Deskripsi uji.',
            'fee' => 250000,
            'quota' => 20,
            'start_date' => '2026-12-01',
            'status' => 'open',
        ], $override);
    }

    private function makeCourse(string $code = 'WEB-001', array $override = []): Course
    {
        return Course::create(array_merge([
            'code' => $code,
            'name' => 'Web Dasar',
            'description' => 'Data uji.',
            'fee' => 300000,
            'quota' => 25,
            'start_date' => '2026-12-01',
            'status' => 'open',
        ], $override));
    }

    // ---- Read ----

    public function test_halaman_detail_menampilkan_data_course(): void
    {
        $course = $this->makeCourse();

        $this->get(route('courses.show', $course))
            ->assertOk()
            ->assertSee('WEB-001')
            ->assertSee('Web Dasar')
            ->assertSee('Rp 300.000');
    }

    public function test_detail_course_tidak_ada_menghasilkan_404(): void
    {
        $this->get('/courses/999999')->assertNotFound();
    }

    public function test_halaman_edit_menampilkan_nilai_lama(): void
    {
        $course = $this->makeCourse();

        $this->get(route('courses.edit', $course))
            ->assertOk()
            ->assertSee('value="WEB-001"', false)
            ->assertSee('value="2026-12-01"', false);
    }

    // ---- Kasus 1-9: validasi create ----

    public function test_01_duplicate_code_saat_create_ditolak(): void
    {
        $this->makeCourse('WEB-001');

        $this->post(route('courses.store'), $this->validData(['code' => 'WEB-001']))
            ->assertSessionHasErrors('code');

        $this->assertSame(1, Course::count());
    }

    public function test_02_nama_kosong_ditolak(): void
    {
        $this->post(route('courses.store'), $this->validData(['name' => '']))
            ->assertSessionHasErrors('name');
    }

    public function test_03_nama_terlalu_pendek_ditolak(): void
    {
        $this->post(route('courses.store'), $this->validData(['name' => 'AI']))
            ->assertSessionHasErrors('name');
    }

    public function test_04_fee_negatif_ditolak(): void
    {
        $this->post(route('courses.store'), $this->validData(['fee' => -1000]))
            ->assertSessionHasErrors('fee');
    }

    public function test_05_fee_bukan_integer_ditolak(): void
    {
        $this->post(route('courses.store'), $this->validData(['fee' => 'abc']))
            ->assertSessionHasErrors('fee');
    }

    public function test_06_quota_nol_ditolak(): void
    {
        $this->post(route('courses.store'), $this->validData(['quota' => 0]))
            ->assertSessionHasErrors('quota');
    }

    public function test_07_quota_501_ditolak(): void
    {
        $this->post(route('courses.store'), $this->validData(['quota' => 501]))
            ->assertSessionHasErrors('quota');
    }

    public function test_08_quota_minimum_1_diterima(): void
    {
        $this->post(route('courses.store'), $this->validData(['quota' => 1]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('courses.index'));

        $this->assertDatabaseHas('courses', ['code' => 'NEW-001', 'quota' => 1]);
    }

    public function test_09_quota_maksimum_500_diterima(): void
    {
        $this->post(route('courses.store'), $this->validData(['quota' => 500]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('courses', ['code' => 'NEW-001', 'quota' => 500]);
    }

    // ---- Kasus 10-11 ----

    public function test_10_tanggal_invalid_ditolak(): void
    {
        $this->post(route('courses.store'), $this->validData(['start_date' => 'bukan-tanggal']))
            ->assertSessionHasErrors('start_date');
    }

    public function test_11_status_invalid_ditolak(): void
    {
        $this->post(route('courses.store'), $this->validData(['status' => 'active']))
            ->assertSessionHasErrors('status');
    }

    // ---- Kasus 12-14: update ----

    public function test_12_update_dengan_code_sendiri_berhasil(): void
    {
        $course = $this->makeCourse('WEB-001');

        $this->put(route('courses.update', $course), $this->validData([
            'code' => 'WEB-001',
            'name' => 'Web Dasar Revisi',
        ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('courses.show', $course));

        $this->assertDatabaseHas('courses', ['id' => $course->id, 'name' => 'Web Dasar Revisi']);
    }

    public function test_13_update_ke_code_course_lain_ditolak(): void
    {
        $this->makeCourse('WEB-001');
        $kedua = $this->makeCourse('PHP-001', ['name' => 'PHP Dasar']);

        $this->put(route('courses.update', $kedua), $this->validData(['code' => 'WEB-001']))
            ->assertSessionHasErrors('code');

        $this->assertDatabaseHas('courses', ['id' => $kedua->id, 'code' => 'PHP-001']);
    }

    public function test_14_update_normal_menampilkan_data_baru_di_detail(): void
    {
        $course = $this->makeCourse();

        $this->followingRedirects()
            ->put(route('courses.update', $course), $this->validData([
                'code' => 'WEB-001',
                'name' => 'Web Lanjutan',
                'fee' => 450000,
                'quota' => 30,
                'status' => 'closed',
            ]))
            ->assertOk()
            ->assertSee('Kursus berhasil diperbarui.')
            ->assertSee('Web Lanjutan')
            ->assertSee('Rp 450.000');
    }

    public function test_update_invalid_mempertahankan_input_lama_di_form_edit(): void
    {
        $course = $this->makeCourse();

        $this->from(route('courses.edit', $course))
            ->followingRedirects()
            ->put(route('courses.update', $course), $this->validData([
                'code' => 'WEB-001',
                'name' => 'Nama Salah Input',
                'quota' => 501,
            ]))
            ->assertSee('Nama Salah Input')
            ->assertSee('is-invalid', false);
    }

    // ---- Kasus 15: delete ----

    public function test_15_delete_menghapus_data_dan_menampilkan_flash(): void
    {
        $course = $this->makeCourse('DEL-001');

        $this->followingRedirects()
            ->delete(route('courses.destroy', $course))
            ->assertOk()
            ->assertSee('Kursus berhasil dihapus.');

        $this->assertDatabaseMissing('courses', ['code' => 'DEL-001']);
    }
}
