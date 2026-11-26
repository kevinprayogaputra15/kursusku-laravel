<script>
// Fitur hiding password: ubah type input antara "password" dan "text".
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-toggle-password]');
    if (!btn) { return; }

    const input = document.querySelector(btn.dataset.togglePassword);
    if (!input) { return; }

    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.textContent = show ? 'Sembunyikan' : 'Tampilkan';
    btn.setAttribute('aria-pressed', show ? 'true' : 'false');
});
</script>
