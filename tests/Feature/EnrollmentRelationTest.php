<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Participant;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test matrix Pertemuan 12: relasi Course - Participant - Enrollment.
 * Jalankan: php artisan test --filter=EnrollmentRelationTest
 */
class EnrollmentRelationTest extends TestCase
{
    use RefreshDatabase;

    private function makeCourse(string $code, string $name, string $status = 'open', int $quota = 20): Course
    {
        return Course::create([
            'code' => $code,
            'name' => $name,
            'description' => 'Data uji.',
            'fee' => 300000,
            'quota' => $quota,
            'start_date' => '2026-12-01',
            'status' => $status,
        ]);
    }

    private function makeParticipant(string $name, string $email): Participant
    {
        return Participant::create(['name' => $name, 'email' => $email]);
    }

    private function enroll(Participant $p, Course $c): Enrollment
    {
        return Enrollment::create([
            'participant_id' => $p->id,
            'course_id' => $c->id,
            'status' => 'registered',
        ]);
    }

    // ---- Skenario 1-8 (test matrix panduan) ----

    public function test_01_form_menampilkan_participant_dan_course_open(): void
    {
        $this->makeParticipant('Alya Rahma', 'alya@example.test');
        $this->makeCourse('LRV-101', 'Laravel Dasar');
        $this->makeCourse('DRF-101', 'Kursus Draft', 'draft');

        $this->get(route('enrollments.create'))
            ->assertOk()
            ->assertSee('Alya Rahma')
            ->assertSee('LRV-101 - Laravel Dasar')
            ->assertDontSee('Kursus Draft');
    }

    public function test_02_participant_kosong_ditolak(): void
    {
        $course = $this->makeCourse('LRV-101', 'Laravel Dasar');

        $this->post(route('enrollments.store'), ['participant_id' => '', 'course_id' => $course->id])
            ->assertSessionHasErrors('participant_id');

        $this->assertSame(0, Enrollment::count());
    }

    public function test_03_course_kosong_ditolak(): void
    {
        $p = $this->makeParticipant('Alya Rahma', 'alya@example.test');

        $this->post(route('enrollments.store'), ['participant_id' => $p->id, 'course_id' => ''])
            ->assertSessionHasErrors('course_id');

        $this->assertSame(0, Enrollment::count());
    }

    public function test_04_alya_ke_laravel_tersimpan_dengan_status_registered(): void
    {
        $alya = $this->makeParticipant('Alya Rahma', 'alya@example.test');
        $laravel = $this->makeCourse('LRV-101', 'Laravel Dasar');

        $this->post(route('enrollments.store'), [
            'participant_id' => $alya->id,
            'course_id' => $laravel->id,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('enrollments.index'));

        $this->assertDatabaseHas('enrollments', [
            'participant_id' => $alya->id,
            'course_id' => $laravel->id,
            'status' => 'registered',
        ]);
    }

    public function test_05_alya_ke_laravel_lagi_ditolak_duplicate(): void
    {
        $alya = $this->makeParticipant('Alya Rahma', 'alya@example.test');
        $laravel = $this->makeCourse('LRV-101', 'Laravel Dasar');
        $this->enroll($alya, $laravel);

        $this->post(route('enrollments.store'), [
            'participant_id' => $alya->id,
            'course_id' => $laravel->id,
        ])->assertSessionHasErrors([
            'course_id' => 'Participant ini sudah terdaftar pada course tersebut.',
        ]);

        $this->assertSame(1, Enrollment::count());
    }

    public function test_06_alya_ke_php_boleh_tersimpan(): void
    {
        $alya = $this->makeParticipant('Alya Rahma', 'alya@example.test');
        $laravel = $this->makeCourse('LRV-101', 'Laravel Dasar');
        $php = $this->makeCourse('PHP-101', 'PHP Dasar');
        $this->enroll($alya, $laravel);

        $this->post(route('enrollments.store'), [
            'participant_id' => $alya->id,
            'course_id' => $php->id,
        ])->assertSessionHasNoErrors();

        $this->assertSame(2, Enrollment::where('participant_id', $alya->id)->count());
    }

    public function test_07_bima_ke_laravel_boleh_tersimpan(): void
    {
        $alya = $this->makeParticipant('Alya Rahma', 'alya@example.test');
        $bima = $this->makeParticipant('Bima Putra', 'bima@example.test');
        $laravel = $this->makeCourse('LRV-101', 'Laravel Dasar');
        $this->enroll($alya, $laravel);

        $this->post(route('enrollments.store'), [
            'participant_id' => $bima->id,
            'course_id' => $laravel->id,
        ])->assertSessionHasNoErrors();

        $this->assertSame(2, Enrollment::where('course_id', $laravel->id)->count());
    }

    public function test_08_laporan_menampilkan_participant_course_start_date_status(): void
    {
        $alya = $this->makeParticipant('Alya Rahma', 'alya@example.test');
        $laravel = $this->makeCourse('LRV-101', 'Laravel Dasar');
        $this->enroll($alya, $laravel);

        $this->get(route('enrollments.index'))
            ->assertOk()
            ->assertSee('Alya Rahma')
            ->assertSee('Laravel Dasar')
            ->assertSee('01/12/2026')
            ->assertSee('registered');
    }

    // ---- Tambahan: validasi dan constraint database ----

    public function test_id_yang_tidak_ada_ditolak_oleh_rule_exists(): void
    {
        $this->post(route('enrollments.store'), ['participant_id' => 9999, 'course_id' => 9999])
            ->assertSessionHasErrors(['participant_id', 'course_id']);
    }

    public function test_database_menolak_pasangan_duplikat_walau_validasi_dilewati(): void
    {
        $alya = $this->makeParticipant('Alya Rahma', 'alya@example.test');
        $laravel = $this->makeCourse('LRV-101', 'Laravel Dasar');
        $this->enroll($alya, $laravel);

        $this->expectException(QueryException::class);

        $this->enroll($alya, $laravel);
    }

    public function test_email_participant_harus_unik_di_database(): void
    {
        $this->makeParticipant('Alya Rahma', 'alya@example.test');

        $this->expectException(QueryException::class);

        $this->makeParticipant('Alya Kembar', 'alya@example.test');
    }

    // ---- Relasi Eloquent ----

    public function test_relasi_eloquent_bekerja_dua_arah(): void
    {
        $alya = $this->makeParticipant('Alya Rahma', 'alya@example.test');
        $laravel = $this->makeCourse('LRV-101', 'Laravel Dasar');
        $enrollment = $this->enroll($alya, $laravel);

        $this->assertTrue($enrollment->participant->is($alya));
        $this->assertTrue($enrollment->course->is($laravel));
        $this->assertCount(1, $alya->enrollments);
        $this->assertCount(1, $laravel->enrollments);
        $this->assertTrue($alya->courses->first()->is($laravel));
        $this->assertTrue($laravel->participants->first()->is($alya));
        $this->assertSame('registered', $alya->courses->first()->pivot->status);
    }

    // ---- Cascade ----

    public function test_cascade_a_hapus_course_menghapus_enrollment_tetapi_participant_tetap(): void
    {
        $participant = $this->makeParticipant('Dummy Cascade Course', 'cascade.course@example.test');
        $course = $this->makeCourse('TEST-CASCADE-COURSE', 'Course Cascade Test');
        $this->enroll($participant, $course);

        $this->assertSame(1, Enrollment::where('course_id', $course->id)->count());

        $course->delete();

        $this->assertSame(0, Enrollment::where('course_id', $course->id)->count());
        $this->assertNotNull(Participant::find($participant->id));
    }

    public function test_cascade_b_hapus_participant_menghapus_enrollment_tetapi_course_tetap(): void
    {
        $participant = $this->makeParticipant('Dummy Participant Delete', 'cascade.participant@example.test');
        $course = $this->makeCourse('TEST-CASCADE-PART', 'Participant Cascade Test');
        $this->enroll($participant, $course);

        $this->assertSame(1, Enrollment::where('participant_id', $participant->id)->count());

        $participant->delete();

        $this->assertSame(0, Enrollment::where('participant_id', $participant->id)->count());
        $this->assertNotNull(Course::find($course->id));
    }

    // ---- Availability (Ajax pertemuan 9) ----

    public function test_availability_menghitung_sisa_kursi_dari_enrollment(): void
    {
        $course = $this->makeCourse('LRV-101', 'Laravel Dasar', 'open', 2);
        $this->enroll($this->makeParticipant('Alya Rahma', 'alya@example.test'), $course);

        $this->getJson(route('courses.availability', $course))
            ->assertOk()
            ->assertJson(['quota' => 2, 'remaining' => 1, 'course' => 'Laravel Dasar']);
    }

    public function test_url_lama_pendaftaran_diarahkan_ke_form_enrollment(): void
    {
        $this->get('/pendaftaran')->assertRedirect('/enrollments/create');
    }
}
