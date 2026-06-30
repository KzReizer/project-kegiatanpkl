const photoInput = document.querySelector('#photo-input');
const photoName = document.querySelector('#photo-name');

if (photoInput && photoName) {
    photoInput.addEventListener('change', () => {
        photoName.textContent = photoInput.files?.[0]?.name || 'Belum ada foto dipilih';
    });
}
