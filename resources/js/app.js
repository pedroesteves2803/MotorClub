import '../css/app.css';

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

const ready = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback, { once: true });
    } else {
        callback();
    }
};

const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

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
    const candidates = document.querySelectorAll('[data-reveal], main > section:not(.mc-home-hero), main > article, main > form, .mc-home-copy > *, .mc-home-poster, .mc-home-features article, .mc-car, .mc-surface');

    candidates.forEach((element, index) => {
        if (element.closest('[data-no-motion]')) return;
        if (element.dataset.mcRevealReady) return;
        element.classList.add('mc-reveal');
        element.style.setProperty('--mc-reveal-delay', `${Math.min(index * 45, 260)}ms`);
        element.dataset.mcRevealReady = 'true';
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

const initImageMotion = () => {
    const images = document.querySelectorAll('main img:not([data-no-motion])');
    if (!images.length) return;

    images.forEach((image) => {
        image.classList.add('mc-image-reveal');
        if (!image.hasAttribute('loading')) image.loading = 'lazy';
        image.decoding = 'async';
        if (image.closest('.mc-surface, .mc-car, figure')) image.classList.add('mc-scroll-zoom');
    });

    if (reduceMotion.matches || !('IntersectionObserver' in window)) {
        images.forEach((image) => image.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries, currentObserver) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            currentObserver.unobserve(entry.target);
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.04 });

    images.forEach((image) => observer.observe(image));
};

const initScrollZoom = () => {
    if (!document.querySelector('.mc-scroll-zoom') || reduceMotion.matches) return;

    const update = () => {
        document.querySelectorAll('.mc-scroll-zoom').forEach((item) => {
            const bounds = item.getBoundingClientRect();
            if (bounds.bottom < 0 || bounds.top > window.innerHeight) return;
            const distance = clamp((window.innerHeight - bounds.top) / (window.innerHeight + bounds.height), 0, 1);
            item.style.setProperty('--mc-scroll-scale', (1 + distance * 0.025).toFixed(4));
        });
    };

    if (document.body.dataset.mcScrollZoomBound) {
        update();
        return;
    }

    let frame = 0;
    const requestUpdate = () => {
        if (frame) return;
        frame = window.requestAnimationFrame(() => {
            frame = 0;
            update();
        });
    };

    document.body.dataset.mcScrollZoomBound = 'true';
    update();
    window.addEventListener('scroll', requestUpdate, { passive: true });
    window.addEventListener('resize', requestUpdate, { passive: true });
};

const initFeedback = () => {
    document.querySelectorAll('[role="status"], [role="alert"], .mc-notice').forEach((element) => {
        if (element.dataset.mcFeedbackReady) return;
        element.classList.add('mc-feedback');
        element.dataset.mcFeedbackReady = 'true';
        window.requestAnimationFrame(() => element.classList.add('is-visible'));
    });
};

const initSectionTabs = () => {
    const tabs = document.querySelector('[data-section-tabs]');
    if (!tabs || tabs.dataset.mcBound) return;

    const links = [...tabs.querySelectorAll('a[data-section-tab]')];
    if (!links.length) return;

    const sync = () => {
        const current = window.location.hash || `#${links[0].getAttribute('href')?.slice(1)}`;
        links.forEach((link) => link.setAttribute('aria-current', link.getAttribute('href') === current ? 'page' : 'false'));
    };

    tabs.dataset.mcBound = 'true';
    links.forEach((link) => link.addEventListener('click', () => window.requestAnimationFrame(sync)));
    window.addEventListener('hashchange', sync, { passive: true });
    sync();
};

const initThemeHeadlights = () => {
    const marks = document.querySelectorAll('[data-theme-headlight]');
    if (!marks.length) return;

    const isDarkTheme = () => document.documentElement.classList.contains('dark') || document.body.classList.contains('dark');
    const sync = () => {
        const dark = isDarkTheme();

        document.querySelectorAll('[data-theme-headlight]').forEach((mark) => {
            const wasReady = mark.dataset.mcThemeHeadlightReady === 'true';
            mark.classList.toggle('is-dark', dark);
            mark.classList.toggle('is-lit', !dark);

            if (wasReady) return;

            mark.dataset.mcThemeHeadlightReady = 'true';
            mark.classList.add('is-initializing', 'is-ready');
            window.requestAnimationFrame(() => mark.classList.remove('is-initializing'));
        });

        document.querySelectorAll('[data-theme-toggle-button]').forEach((control) => {
            const state = dark
                ? 'Tema escuro, farol apagado. Ativar tema claro.'
                : 'Tema claro, farol aceso. Ativar tema escuro.';

            control.classList.toggle('is-dark', dark);
            control.classList.toggle('is-lit', !dark);
            control.dataset.themeToggleState = dark ? 'dark' : 'light';
            control.setAttribute('aria-label', state);
            control.setAttribute('title', state);
            control.setAttribute('aria-pressed', String(!dark));
            control.querySelector('[data-theme-toggle-status]')?.replaceChildren(document.createTextNode(state));
        });
    };

    sync();
    if (document.body.dataset.mcThemeHeadlightsBound) return;

    document.body.dataset.mcThemeHeadlightsBound = 'true';
    const observer = new MutationObserver(sync);
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
    document.addEventListener('change', (event) => {
        if (event.target instanceof HTMLElement && event.target.matches('[data-theme-toggle]')) window.requestAnimationFrame(sync);
    }, true);
};

const initScrollProgress = () => {
    const progress = document.querySelector('.mc-scroll-progress');
    if (!progress || progress.dataset.mcBound) return;

    const update = () => {
        const scrollable = document.documentElement.scrollHeight - window.innerHeight;
        const value = scrollable > 0 ? Math.min(window.scrollY / scrollable, 1) : 0;
        progress.style.setProperty('--mc-scroll-progress', String(value));
    };

    progress.dataset.mcBound = 'true';

    if (!document.body.dataset.mcScrollProgressBound) {
        let frame = 0;
        const requestUpdate = () => {
            if (frame) return;
            frame = window.requestAnimationFrame(() => {
                frame = 0;
                update();
            });
        };

        document.body.dataset.mcScrollProgressBound = 'true';
        document.body._mcScrollProgressRequestUpdate = requestUpdate;
        window.addEventListener('scroll', requestUpdate, { passive: true });
        window.addEventListener('resize', requestUpdate, { passive: true });
    }

    update();
};

const initMenu = () => {
    const nav = document.querySelector('[data-site-nav]');
    const toggle = document.querySelector('[data-menu-toggle]');
    if (!nav || !toggle || nav.dataset.mcBound) return;

    nav.dataset.mcBound = 'true';

    nav.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
        if (window.matchMedia('(max-width: 1023px)').matches) toggle.click();
    }));
};

const initHeroMotion = () => {
    const hero = document.querySelector('[data-hero-animation]');
    if (!hero || reduceMotion.matches || !window.matchMedia('(hover: hover) and (pointer: fine)').matches || hero.dataset.mcBound) return;

    hero.dataset.mcBound = 'true';
    let frame = 0;
    let pointer = { x: 0, y: 0 };

    const applyPointer = () => {
        frame = 0;
        const bounds = hero.getBoundingClientRect();
        const x = ((pointer.x - bounds.left) / bounds.width - 0.5) * 2;
        const y = ((pointer.y - bounds.top) / bounds.height - 0.5) * 2;
        hero.style.setProperty('--mc-pointer-x', `${(x * 8).toFixed(2)}px`);
        hero.style.setProperty('--mc-pointer-y', `${(y * 8).toFixed(2)}px`);
        hero.style.setProperty('--mc-tilt-x', `${(-y * 3.5).toFixed(2)}deg`);
        hero.style.setProperty('--mc-tilt-y', `${(x * 4.5).toFixed(2)}deg`);
    };

    const schedulePointer = () => {
        if (!frame) frame = window.requestAnimationFrame(applyPointer);
    };

    hero.addEventListener('pointermove', (event) => {
        pointer = { x: event.clientX, y: event.clientY };
        schedulePointer();
    });

    hero.addEventListener('pointerleave', () => {
        pointer = { x: 0, y: 0 };
        if (frame) window.cancelAnimationFrame(frame);
        frame = 0;
        hero.style.setProperty('--mc-pointer-x', '0px');
        hero.style.setProperty('--mc-pointer-y', '0px');
        hero.style.setProperty('--mc-tilt-x', '0deg');
        hero.style.setProperty('--mc-tilt-y', '0deg');
    });
};

const initCardTilt = () => {
    if (reduceMotion.matches || !window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;

    const cards = document.querySelectorAll('main .mc-car, main article.mc-surface, main a.mc-surface');
    cards.forEach((card) => {
        if (card.dataset.mcTiltBound || card.matches('.mc-form') || card.querySelector('form, input, textarea, select, button')) return;

        card.dataset.mcTiltBound = 'true';
        card.classList.add('mc-tilt-card');
        let frame = 0;
        let pointer = { x: 0, y: 0 };

        const reset = () => {
            if (frame) window.cancelAnimationFrame(frame);
            frame = 0;
            card.style.setProperty('--mc-card-tilt-x', '0deg');
            card.style.setProperty('--mc-card-tilt-y', '0deg');
        };

        const apply = () => {
            frame = 0;
            const bounds = card.getBoundingClientRect();
            if (!bounds.width || !bounds.height) return;
            const x = ((pointer.x - bounds.left) / bounds.width - 0.5) * 2;
            const y = ((pointer.y - bounds.top) / bounds.height - 0.5) * 2;
            card.style.setProperty('--mc-card-tilt-x', `${(-y * 1.8).toFixed(2)}deg`);
            card.style.setProperty('--mc-card-tilt-y', `${(x * 2.2).toFixed(2)}deg`);
        };

        card.addEventListener('pointermove', (event) => {
            pointer = { x: event.clientX, y: event.clientY };
            if (!frame) frame = window.requestAnimationFrame(apply);
        }, { passive: true });
        card.addEventListener('pointerleave', reset, { passive: true });
    });
};

const initLightShowroom = () => {
    document.querySelectorAll('[data-light-showroom]').forEach((root) => {
        if (root.dataset.mcLightShowroomReady) return;

        root.dataset.mcLightShowroomReady = 'true';
        const activate = () => root.classList.add('is-active');

        if (reduceMotion.matches || !('IntersectionObserver' in window)) {
            activate();
            return;
        }

        const visibility = new IntersectionObserver(([entry], observer) => {
            if (!entry.isIntersecting) return;
            activate();
            observer.disconnect();
        }, { threshold: 0.2 });

        visibility.observe(root);
        root._mcLightShowroomCleanup = () => {
            visibility.disconnect();
            root.classList.remove('is-active');
            delete root._mcLightShowroomCleanup;
        };
    });
};

const initEasterEggs = () => {
    if (document.body.dataset.mcEasterEggsBound) return;

    const sequence = 'BLUEHOUR';
    let input = '';
    let resetTimer;
    const wordmark = document.querySelector('.mc-wordmark');

    document.body.dataset.mcEasterEggsBound = 'true';

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

    wordmark?.addEventListener('dblclick', (event) => {
        event.preventDefault();
        event.stopPropagation();
        activate();
    });
};

const initMotion = () => {
    initReveal();
    initImageMotion();
    initScrollZoom();
    initFeedback();
    initSectionTabs();
    initThemeHeadlights();
    initScrollProgress();
    initMenu();
    initHeroMotion();
    initCardTilt();
    initLightShowroom();
    initEasterEggs();
};

const cleanupLightShowrooms = () => {
    document.querySelectorAll('[data-light-showroom]').forEach((root) => root._mcLightShowroomCleanup?.());
};

ready(() => {
    initMotion();
    document.addEventListener('livewire:navigating', cleanupLightShowrooms);
    document.addEventListener('livewire:navigated', initMotion);
});
