// Fitur "hiding password": tombol mata menampilkan / menyembunyikan password.
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.password-toggle');
    if (!btn) return;

    const input = document.getElementById(btn.dataset.target);
    if (!input) return;

    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.classList.toggle('is-visible', show);
    btn.setAttribute('aria-pressed', String(show));
    btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
});
