

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const debounce = (callback, delay = 450) => {
    let timer;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => callback(...args), delay);
    };
};

document.querySelectorAll('.auto-grow').forEach((textarea) => {
    const grow = () => {
        textarea.style.height = 'auto';
        textarea.style.height = `${textarea.scrollHeight}px`;
    };

    textarea.addEventListener('input', grow);
    grow();
});

document.querySelectorAll('[data-realtime-filter]').forEach((form) => {
    const submit = debounce(() => form.requestSubmit(), 550);

    form.querySelectorAll('input[type="search"]').forEach((input) => {
        input.addEventListener('input', submit);
    });

    form.querySelectorAll('select, input[type="month"]').forEach((input) => {
        input.addEventListener('change', () => form.requestSubmit());
    });
});

const compressImage = (file) => new Promise((resolve) => {
    if (!file.type.startsWith('image/') || file.size < 700 * 1024) {
        resolve(file);
        return;
    }

    const image = new Image();
    const url = URL.createObjectURL(file);

    image.onload = () => {
        const maxSize = 1600;
        const ratio = Math.min(maxSize / image.width, maxSize / image.height, 1);
        const width = Math.round(image.width * ratio);
        const height = Math.round(image.height * ratio);
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');

        canvas.width = width;
        canvas.height = height;
        context.drawImage(image, 0, 0, width, height);

        canvas.toBlob((blob) => {
            URL.revokeObjectURL(url);

            if (!blob) {
                resolve(file);
                return;
            }

            resolve(new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), {
                type: 'image/jpeg',
                lastModified: Date.now(),
            }));
        }, 'image/jpeg', 0.82);
    };

    image.onerror = () => {
        URL.revokeObjectURL(url);
        resolve(file);
    };

    image.src = url;
});

const renderPreview = (field, files) => {
    const preview = field?.querySelector('[data-upload-preview]');
    const photoName = field?.querySelector('.photo-name');

    if (photoName) {
        photoName.textContent = files.length
            ? `${files.length} foto dipilih`
            : 'Belum ada foto dipilih';
    }

    if (!preview) {
        return;
    }

    preview.innerHTML = '';

    files.slice(0, 6).forEach((file) => {
        const image = document.createElement('img');
        image.alt = file.name;
        image.src = URL.createObjectURL(file);
        image.addEventListener('load', () => URL.revokeObjectURL(image.src), { once: true });
        preview.appendChild(image);
    });
};

const replaceInputFiles = (input, files) => {
    if (typeof DataTransfer === 'undefined') {
        renderPreview(input.closest('.file-field'), files);
        return;
    }

    const transfer = new DataTransfer();
    files.forEach((file) => transfer.items.add(file));
    input.files = transfer.files;
    renderPreview(input.closest('.file-field'), files);
};

document.querySelectorAll('.photo-input').forEach((photoInput) => {
    const field = photoInput.closest('.file-field');

    const processFiles = async (fileList) => {
        const files = Array.from(fileList).filter((file) => file.type.startsWith('image/'));
        const compressed = await Promise.all(files.map((file) => compressImage(file)));
        replaceInputFiles(photoInput, compressed);
    };

    photoInput.addEventListener('change', () => processFiles(photoInput.files));

    if (!field) {
        return;
    }

    field.addEventListener('dragover', (event) => {
        event.preventDefault();
        field.classList.add('is-dragging');
    });

    field.addEventListener('dragleave', () => {
        field.classList.remove('is-dragging');
    });

    field.addEventListener('drop', (event) => {
        event.preventDefault();
        field.classList.remove('is-dragging');

        if (event.dataTransfer?.files?.length) {
            processFiles(event.dataTransfer.files);
        }
    });
});

const modal = document.querySelector('[data-photo-modal]');
const modalImage = document.querySelector('[data-photo-modal-image]');

document.querySelectorAll('[data-photo-src]').forEach((button) => {
    button.addEventListener('click', () => {
        modalImage.src = button.dataset.photoSrc;
        modalImage.alt = button.dataset.photoAlt || 'Preview dokumentasi';
        modal.hidden = false;
    });
});

document.querySelectorAll('[data-photo-close]').forEach((button) => {
    button.addEventListener('click', () => {
        modal.hidden = true;
        modalImage.src = '';
    });
});

modal?.addEventListener('click', (event) => {
    if (event.target === modal) {
        modal.hidden = true;
        modalImage.src = '';
    }
});

const revealObserver = 'IntersectionObserver' in window
    ? new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 })
    : null;

document.querySelectorAll('.reveal-on-scroll').forEach((element) => {
    revealObserver?.observe(element);
});
