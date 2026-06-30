document.querySelectorAll('.photo-input').forEach((photoInput) => {
    const photoName = photoInput.closest('.file-field')?.querySelector('.photo-name');

    if (!photoName) {
        return;
    }

    photoInput.addEventListener('change', () => {
        photoName.textContent = photoInput.files?.[0]?.name || 'Belum ada foto dipilih';
    });
});
