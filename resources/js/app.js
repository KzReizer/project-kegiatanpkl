

import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;

Alpine.start();

const renderIcons = () => {
    createIcons({
        icons,
        attrs: {
            'aria-hidden': 'true',
        },
    });
};

const root = document.documentElement;
const getStoredTheme = () => localStorage.getItem('theme');
const prefersDark = () => window.matchMedia?.('(prefers-color-scheme: dark)').matches;
const activeTheme = () => getStoredTheme() || (prefersDark() ? 'dark' : 'light');

const setTheme = (theme) => {
    root.classList.toggle('dark', theme === 'dark');
    root.classList.toggle('light', theme === 'light');

    document.querySelectorAll('[data-theme-icon]').forEach((icon) => {
        icon.innerHTML = `<i data-lucide="${theme === 'dark' ? 'sun' : 'moon'}"></i>`;
    });

    renderIcons();
};

setTheme(activeTheme());

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const nextTheme = root.classList.contains('dark') ? 'light' : 'dark';
        localStorage.setItem('theme', nextTheme);
        setTheme(nextTheme);
    });
});

const nav = document.querySelector('[data-site-nav]');
const syncNav = () => nav?.classList.toggle('is-scrolled', window.scrollY > 8);
syncNav();
window.addEventListener('scroll', syncNav, { passive: true });

const animateCounter = (counter) => {
    const target = Number.parseInt(counter.dataset.counter || counter.textContent, 10);

    if (!Number.isFinite(target)) {
        return;
    }

    const formatter = new Intl.NumberFormat('id-ID');

    if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
        counter.textContent = formatter.format(target);
        return;
    }

    const duration = 900;
    const start = performance.now();

    const tick = (time) => {
        const progress = Math.min((time - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        counter.textContent = formatter.format(Math.round(target * eased));

        if (progress < 1) {
            requestAnimationFrame(tick);
        }
    };

    requestAnimationFrame(tick);
};

const counterObserver = 'IntersectionObserver' in window
    ? new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting || entry.target.dataset.counted) {
                return;
            }

            entry.target.dataset.counted = 'true';
            animateCounter(entry.target);
            counterObserver.unobserve(entry.target);
        });
    }, { threshold: 0.45 })
    : null;

document.querySelectorAll('[data-counter]').forEach((counter) => {
    if (counterObserver) {
        counterObserver.observe(counter);
    } else {
        animateCounter(counter);
    }
});

document.addEventListener('click', (event) => {
    const target = event.target.closest('.primary-button, .secondary-button, .danger-button, .icon-button, .theme-toggle, .mobile-menu-button, .user-menu-button');

    if (!target || target.disabled) {
        return;
    }

    const rect = target.getBoundingClientRect();
    const ripple = document.createElement('span');

    ripple.className = 'button-ripple';
    ripple.style.left = `${event.clientX - rect.left}px`;
    ripple.style.top = `${event.clientY - rect.top}px`;

    target.appendChild(ripple);
    ripple.addEventListener('animationend', () => ripple.remove(), { once: true });
});

document.querySelectorAll('[data-toast]').forEach((toast) => {
    const close = () => {
        toast.classList.add('is-leaving');
        toast.addEventListener('animationend', () => toast.remove(), { once: true });
    };

    toast.querySelector('[data-toast-close]')?.addEventListener('click', close);
    window.setTimeout(close, 4200);
});

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

renderIcons();
