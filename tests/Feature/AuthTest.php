<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * UAS Project Akhir: login, hash password, hiding password, remember me,
 * forgot password, dashboard/profil, logout.
 * Jalankan: php artisan test --filter=AuthTest
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $override = []): User
    {
        return User::create(array_merge([
            'name' => 'Admin Uji',
            'email' => 'admin@kursusku.test',
            'password' => 'Admin12345',
        ], $override));
    }

    // ---- 1. Halaman login & hiding password ----

    public function test_halaman_login_dapat_dibuka_dan_memuat_fitur_hiding_password(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk ke KursusKu')
            ->assertSee('type="password"', false)
            ->assertSee('data-toggle-password', false)
            ->assertSee('Ingat saya')
            ->assertSee('Lupa password?');
    }

    // ---- 2. User & password hash ----

    public function test_password_tersimpan_sebagai_hash_bukan_teks_biasa(): void
    {
        $user = $this->makeUser();

        $this->assertNotSame('Admin12345', $user->password);
        $this->assertStringStartsWith('$2y$', $user->password);
        $this->assertTrue(Hash::check('Admin12345', $user->password));
        $this->assertDatabaseMissing('users', ['password' => 'Admin12345']);
    }

    // ---- Login ----

    public function test_login_berhasil_diarahkan_ke_dashboard(): void
    {
        $user = $this->makeUser();

        $this->post(route('login.store'), [
            'email' => 'admin@kursusku.test',
            'password' => 'Admin12345',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_gagal_password_salah(): void
    {
        $this->makeUser();

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'admin@kursusku.test',
                'password' => 'salah-total',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Email atau password salah.']);

        $this->assertGuest();
    }

    public function test_login_gagal_email_tidak_terdaftar(): void
    {
        $this->post(route('login.store'), [
            'email' => 'tidakada@kursusku.test',
            'password' => 'Admin12345',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_validasi_field_kosong(): void
    {
        $this->post(route('login.store'), ['email' => '', 'password' => ''])
            ->assertSessionHasErrors(['email', 'password']);
    }

    // ---- 3. Remember me ----

    public function test_remember_me_membuat_cookie_ingat_saya(): void
    {
        $this->makeUser();
        $recaller = Auth::guard('web')->getRecallerName();

        $this->post(route('login.store'), [
            'email' => 'admin@kursusku.test',
            'password' => 'Admin12345',
            'remember' => '1',
        ])->assertCookie($recaller);
    }

    public function test_tanpa_remember_me_tidak_ada_cookie_ingat_saya(): void
    {
        $this->makeUser();
        $recaller = Auth::guard('web')->getRecallerName();

        $this->post(route('login.store'), [
            'email' => 'admin@kursusku.test',
            'password' => 'Admin12345',
        ])->assertCookieMissing($recaller);
    }

    // ---- 4. Forgot password ----

    public function test_halaman_lupa_password_dapat_dibuka(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Lupa Password');
    }

    public function test_lupa_password_mengirim_notifikasi_reset_ke_user_terdaftar(): void
    {
        Notification::fake();
        $user = $this->makeUser();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('success');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_email_reset_berbahasa_indonesia_dan_memuat_tautan_reset(): void
    {
        $user = $this->makeUser();
        $notification = new ResetPasswordNotification('token-contoh');

        $mail = $notification->toMail($user);

        $this->assertSame('Reset Password KursusKu', $mail->subject);
        $this->assertSame('Halo, Admin Uji!', $mail->greeting);
        $this->assertStringContainsString('/reset-password/token-contoh', $mail->actionUrl);
        $this->assertStringContainsString('admin%40kursusku.test', $mail->actionUrl);
    }

    public function test_lupa_password_email_tidak_terdaftar_tidak_membocorkan_informasi(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'tidakada@kursusku.test'])
            ->assertSessionHas('success')
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_reset_password_dengan_token_valid_mengubah_password(): void
    {
        $user = $this->makeUser();
        $token = Password::createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee('Buat Password Baru');

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('PasswordBaru123', $user->fresh()->password));
    }

    public function test_reset_password_dengan_token_salah_ditolak(): void
    {
        $user = $this->makeUser();

        $this->post(route('password.update'), [
            'token' => 'token-ngawur',
            'email' => $user->email,
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('Admin12345', $user->fresh()->password));
    }

    public function test_reset_password_konfirmasi_tidak_sama_ditolak(): void
    {
        $user = $this->makeUser();
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'Berbeda12345',
        ])->assertSessionHasErrors('password');
    }

    // ---- 5. Dashboard / profil + proteksi ----

    public function test_dashboard_tidak_bisa_dibuka_tanpa_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_menampilkan_profil_setelah_login(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Profil Saya')
            ->assertSee('Admin Uji')
            ->assertSee('admin@kursusku.test')
            ->assertSee('Keluar');
    }

    public function test_pengguna_yang_sudah_login_diarahkan_dari_halaman_login_ke_dashboard(): void
    {
        $this->actingAs($this->makeUser())
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));
    }

    // ---- 6. Logout / login di navbar ----

    public function test_navbar_tamu_menampilkan_tombol_masuk(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Masuk')
            ->assertDontSee('Keluar (');
    }

    public function test_navbar_user_login_menampilkan_tombol_keluar(): void
    {
        $this->actingAs($this->makeUser())
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Keluar (Admin Uji)')
            ->assertSee('Dashboard');
    }

    public function test_logout_mengakhiri_sesi(): void
    {
        $this->actingAs($this->makeUser())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    // ---- 7. Semua fitur navigasi tetap bisa diakses ----

    public function test_halaman_navigasi_lama_tetap_dapat_diakses(): void
    {
        foreach (['home', 'courses.index', 'courses.create', 'enrollments.index', 'enrollments.create'] as $name) {
            $this->get(route($name))->assertOk();
        }

        $this->get('/pendaftaran')->assertRedirect('/enrollments/create');
    }

    // ---- 8. Ubah / Reset Password dari halaman Profil Saya ----

    public function test_user_login_dapat_mengubah_password_di_halaman_profil(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->from(route('profile.show'))
            ->put(route('profile.password.update'), [
                'current_password' => 'Admin12345',
                'password' => 'PasswordBaru99',
                'password_confirmation' => 'PasswordBaru99',
            ])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('PasswordBaru99', $user->fresh()->password));
    }

    public function test_ubah_password_di_profil_gagal_jika_password_lama_salah(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->from(route('profile.show'))
            ->put(route('profile.password.update'), [
                'current_password' => 'Salah12345',
                'password' => 'PasswordBaru99',
                'password_confirmation' => 'PasswordBaru99',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('Admin12345', $user->fresh()->password));
    }
}
