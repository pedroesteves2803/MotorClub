import '../css/app.css';

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

const ready = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback, { once: true });
    } else {
        callback();
    }
};

const showToast = (message) => {
    let toast = document.querySelector('[data-mc-toast]');

    if (!toast) {
        toast = document.createElement('div');
        toast.dataset.mcToast = '';
        toast.className = 'mc-toast';
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        document.body.append(toast);
    }

    toast.textContent = message;
    toast.classList.add('is-visible');
    window.clearTimeout(Number(toast.dataset.timeout));
    toast.dataset.timeout = String(window.setTimeout(() => toast.classList.remove('is-visible'), 3600));
};

const initReveal = () => {
    const candidates = document.querySelectorAll('[data-reveal], main > section:not(.mc-home-hero), main > article, main > form, .mc-home-features article, .mc-car, .mc-surface');

    candidates.forEach((element, index) => {
        if (element.closest('[data-no-motion]')) return;
        element.classList.add('mc-reveal');
        element.style.setProperty('--mc-reveal-delay', `${Math.min(index * 45, 260)}ms`);
    });

    if (reduceMotion.matches || !('IntersectionObserver' in window)) {
        candidates.forEach((element) => element.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries, currentObserver) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            currentObserver.unobserve(entry.target);
        });
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.08 });

    candidates.forEach((element) => observer.observe(element));
};

const initScrollProgress = () => {
    const progress = document.querySelector('.mc-scroll-progress');
    if (!progress) return;

    const update = () => {
        const scrollable = document.documentElement.scrollHeight - window.innerHeight;
        const value = scrollable > 0 ? Math.min(window.scrollY / scrollable, 1) : 0;
        progress.style.setProperty('--mc-scroll-progress', String(value));
    };

    update();
    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update, { passive: true });
};

const initMenu = () => {
    const nav = document.querySelector('[data-site-nav]');
    const toggle = document.querySelector('[data-menu-toggle]');
    if (!nav || !toggle) return;

    nav.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
        if (window.matchMedia('(max-width: 1023px)').matches) toggle.click();
    }));
};

const initHeroMotion = () => {
    const hero = document.querySelector('.mc-home-poster');
    if (!hero || reduceMotion.matches || !window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;

    hero.addEventListener('pointermove', (event) => {
        const bounds = hero.getBoundingClientRect();
        const x = ((event.clientX - bounds.left) / bounds.width - 0.5) * 2;
        const y = ((event.clientY - bounds.top) / bounds.height - 0.5) * 2;
        hero.style.setProperty('--mc-pointer-x', `${(x * 8).toFixed(2)}px`);
        hero.style.setProperty('--mc-pointer-y', `${(y * 8).toFixed(2)}px`);
    });

    hero.addEventListener('pointerleave', () => {
        hero.style.setProperty('--mc-pointer-x', '0px');
        hero.style.setProperty('--mc-pointer-y', '0px');
    });
};

const initEasterEggs = () => {
    const sequence = 'BLUEHOUR';
    let input = '';
    let resetTimer;
    const wordmark = document.querySelector('.mc-wordmark');

    const activate = () => {
        document.body.classList.toggle('mc-secret-mode');
        const enabled = document.body.classList.contains('mc-secret-mode');
        showToast(enabled ? 'Blue hour ativada · pit lane mode' : 'Blue hour pausada');
    };

    document.addEventListener('keydown', (event) => {
        if (event.metaKey || event.ctrlKey || event.altKey) return;
        if (event.target instanceof HTMLElement && event.target.matches('input, textarea, select, [contenteditable="true"]')) return;
        input = `${input}${event.key.toUpperCase()}`.slice(-sequence.length);
        window.clearTimeout(resetTimer);
        resetTimer = window.setTimeout(() => { input = ''; }, 1600);
        if (input === sequence) {
            input = '';
            activate();
        }
    });

    wordmark?.addEventListener('dblclick', () => {
        activate();
    });
};

ready(() => {
    initReveal();
    initScrollProgress();
    initMenu();
    initHeroMotion();
    initEasterEggs();
});
