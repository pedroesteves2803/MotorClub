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

// Content and photographs remain visible without scroll-triggered reveals.
const initImageMotion = () => {
    document.querySelectorAll('main img').forEach((image) => {
        if (!image.hasAttribute('loading')) image.loading = 'lazy';
        image.decoding = 'async';
    });
};

const initFeedback = () => {
    document.querySelectorAll('[role="status"], [role="alert"], .mc-notice').forEach((element) => {
        if (element.dataset.mcFeedbackReady) return;
        element.classList.add('mc-feedback');
        element.dataset.mcFeedbackReady = 'true';
        window.requestAnimationFrame(() => element.classList.add('is-visible'));
    });
};

const initPhotoUploaders = () => {
    document.querySelectorAll('[data-photo-uploader]').forEach((root) => {
        if (root.dataset.mcPhotoUploaderReady) return;

        const input = root.querySelector('[data-photo-input]');
        const dropzone = root.querySelector('[data-photo-dropzone]');
        const preview = root.querySelector('[data-photo-preview]');
        const count = root.querySelector('[data-photo-count]');
        const status = root.querySelector('[data-photo-status]');
        if (!input || !dropzone || !preview) return;

        root.dataset.mcPhotoUploaderReady = 'true';
        const maxFiles = Number(root.dataset.maxFiles || 12);
        const accepted = (input.accept || '').split(',').map((type) => type.trim().toLowerCase()).filter(Boolean);
        let files = [];
        let internalChange = false;

        const fileKey = (file) => `${file.name}:${file.size}:${file.lastModified}`;
        const isAccepted = (file) => {
            if (!accepted.length || accepted.includes('*/*')) return true;
            const extension = `.${file.name.split('.').pop()?.toLowerCase() || ''}`;
            return accepted.includes(file.type.toLowerCase()) || accepted.includes(extension);
        };

        const syncInput = (notify = true) => {
            const transfer = new DataTransfer();
            files.forEach((file) => transfer.items.add(file));
            input.files = transfer.files;
            if (notify) {
                internalChange = true;
                input.dispatchEvent(new Event('change', {bubbles: true}));
                window.queueMicrotask(() => { internalChange = false; });
            }
        };

        const render = () => {
            preview.replaceChildren();
            files.forEach((file, index) => {
                const card = document.createElement('figure');
                card.className = 'mc-photo-uploader__item';
                const image = document.createElement('img');
                image.alt = file.name;
                image.loading = 'lazy';
                image.src = URL.createObjectURL(file);
                image.addEventListener('load', () => URL.revokeObjectURL(image.src), {once: true});
                const details = document.createElement('figcaption');
                details.textContent = `${file.name} · ${(file.size / 1024 / 1024).toFixed(1)} MB`;
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'mc-photo-uploader__remove';
                remove.setAttribute('aria-label', `Remover ${file.name}`);
                remove.textContent = 'Remover';
                remove.addEventListener('click', () => {
                    files.splice(index, 1);
                    syncInput();
                    render();
                });
                card.append(image, details, remove);
                preview.append(card);
            });
            if (count) count.textContent = files.length ? `${files.length} de ${maxFiles} fotos selecionadas` : 'Nenhuma foto selecionada';
            if (status && !status.dataset.mcPhotoError) status.textContent = files.length ? 'Revise as miniaturas antes de salvar. Você ainda pode adicionar mais fotos.' : '';
        };

        const addFiles = (incoming) => {
            const rejected = [];
            const existing = new Set(files.map(fileKey));
            [...incoming].forEach((file) => {
                if (!isAccepted(file)) { rejected.push(`${file.name}: formato não permitido`); return; }
                if (existing.has(fileKey(file))) return;
                if (files.length >= maxFiles) { rejected.push(`${file.name}: limite de ${maxFiles} fotos`); return; }
                files.push(file);
                existing.add(fileKey(file));
            });
            if (status) {
                status.dataset.mcPhotoError = rejected.length ? 'true' : '';
                status.textContent = rejected.length ? rejected.join(' · ') : '';
                status.classList.toggle('text-red-600', rejected.length > 0);
            }
            syncInput();
            render();
        };

        dropzone.addEventListener('click', () => input.click());
        dropzone.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); input.click(); }
        });
        input.addEventListener('change', (event) => { if (!internalChange) addFiles(event.target.files); });
        ['dragenter', 'dragover'].forEach((eventName) => dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.add('is-dragging');
        }));
        ['dragleave', 'drop'].forEach((eventName) => dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.remove('is-dragging');
        }));
        dropzone.addEventListener('drop', (event) => addFiles(event.dataTransfer.files));
        input.form?.addEventListener('submit', () => {
            root.classList.add('is-uploading');
            if (status) status.textContent = `Enviando ${files.length || 'as'} fotos…`;
        });
        render();
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
                ? 'Ativar tema claro'
                : 'Ativar tema escuro';

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

const initMagazine = () => {
    document.querySelectorAll('[data-av-magazine]').forEach((root) => {
        if (root.dataset.avMagazineReady) return;

        const book = root.querySelector('[data-av-book]');
        const slots = [...root.querySelectorAll('[data-av-page]')];
        const experience = root.querySelector('[data-av-magazine-view]');
        const reading = root.querySelector('[data-av-reading-view]');
        const total = slots.length;
        if (!book || !slots.length || !experience || !reading) return;

        root.dataset.avMagazineReady = 'true';
        let index = 0;
        let mode = window.localStorage.getItem('autoverse-magazine-mode') || root.dataset.avDefaultMode || 'magazine';
        let animationTimer;

        const isMobile = () => window.matchMedia('(max-width: 860px)').matches;
        const visibleIndexes = () => {
            const count = isMobile() ? 1 : 2;
            return Array.from({ length: count }, (_, offset) => index + offset).filter((value) => value < total);
        };

        const pageLabel = (slot) => {
            const heading = slot?.querySelector('h2');
            return heading?.textContent?.trim() || (slot?.querySelector('h1')?.textContent?.trim() ?? 'Página');
        };

        const sync = ({animate = false} = {}) => {
            const visible = visibleIndexes();
            slots.forEach((slot, slotIndex) => {
                const shouldShow = visible.includes(slotIndex);
                slot.hidden = !shouldShow;
                slot.setAttribute('aria-hidden', String(!shouldShow));
                slot.classList.remove('is-entering');
                if (animate && shouldShow && slotIndex === index && !reduceMotion.matches) {
                    window.requestAnimationFrame(() => slot.classList.add('is-entering'));
                }
            });

            const previous = root.querySelector('[data-av-prev]');
            const next = root.querySelector('[data-av-next]');
            if (previous) previous.disabled = index <= 0;
            if (next) next.disabled = index >= total - 1;

            const progress = root.querySelector('[data-av-progress-bar]');
            if (progress) progress.style.transform = `scaleX(${total > 1 ? index / (total - 1) : 1})`;
            const label = root.querySelector('[data-av-progress-label]');
            if (label) label.textContent = `Página ${String(index + 1).padStart(2, '0')} de ${String(total).padStart(2, '0')}`;
            const status = root.querySelector('[data-av-page-status]');
            if (status) status.textContent = `${pageLabel(slots[index])}. Página ${index + 1} de ${total}.`;
        };

        const setMode = (nextMode) => {
            mode = nextMode === 'reading' ? 'reading' : 'magazine';
            experience.hidden = mode !== 'magazine';
            reading.hidden = mode !== 'reading';
            root.querySelectorAll('[data-av-mode]').forEach((button) => {
                const active = button.dataset.avMode === mode;
                button.classList.toggle('is-active', active);
                button.setAttribute('aria-pressed', String(active));
            });
            window.localStorage.setItem('autoverse-magazine-mode', mode);
            if (mode === 'reading') reading.querySelector('h2')?.focus?.({preventScroll: true});
        };

        const goTo = (target, animate = true) => {
            const nextIndex = clamp(Number(target), 0, total - 1);
            if (nextIndex === index) return;
            index = nextIndex;
            window.clearTimeout(animationTimer);
            sync({animate});
            animationTimer = window.setTimeout(() => slots.forEach((slot) => slot.classList.remove('is-entering')), 680);
        };

        root.querySelectorAll('[data-av-mode]').forEach((button) => button.addEventListener('click', () => setMode(button.dataset.avMode)));
        root.querySelector('[data-av-prev]')?.addEventListener('click', () => goTo(index - 1));
        root.querySelector('[data-av-next]')?.addEventListener('click', () => goTo(index + 1));
        root.querySelectorAll('[data-av-toc]').forEach((button) => button.addEventListener('click', () => {
            setMode('magazine');
            goTo(button.dataset.avToc);
        }));
        book.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft' || event.key === 'PageUp') { event.preventDefault(); goTo(index - 1); }
            if (event.key === 'ArrowRight' || event.key === 'PageDown' || event.key === ' ') { event.preventDefault(); goTo(index + 1); }
            if (event.key === 'Home') { event.preventDefault(); goTo(0, false); }
            if (event.key === 'End') { event.preventDefault(); goTo(total - 1, false); }
        });
        book.addEventListener('click', (event) => {
            if (event.target.closest('button, a')) return;
            const bounds = book.getBoundingClientRect();
            goTo(event.clientX < bounds.left + bounds.width / 2 ? index - 1 : index + 1);
        });
        window.addEventListener('resize', () => sync(), {passive: true});
        setMode(mode);
        sync();
    });
};

const initShareLinks = () => {
    document.querySelectorAll('[data-copy-link]').forEach((button) => {
        if (button.dataset.mcCopyReady) return;
        button.dataset.mcCopyReady = 'true';
        button.addEventListener('click', async () => {
            const value = button.dataset.copyLink;
            try {
                await navigator.clipboard.writeText(value);
                showToast('Link copiado.');
            } catch {
                window.prompt('Copie o link público:', value);
            }
        });
    });
    document.querySelectorAll('[data-native-share]').forEach((button) => {
        if (button.dataset.mcNativeShareReady) return;
        button.dataset.mcNativeShareReady = 'true';
        button.addEventListener('click', async () => {
            if (!navigator.share) {
                showToast('O compartilhamento nativo não está disponível neste navegador.');
                return;
            }
            try {
                await navigator.share({title: button.dataset.nativeShareTitle || 'Autoverse', url: button.dataset.nativeShare});
            } catch (error) {
                if (error?.name !== 'AbortError') showToast('Não foi possível abrir o compartilhamento.');
            }
        });
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
    initImageMotion();
    initFeedback();
    initPhotoUploaders();
    initSectionTabs();
    initThemeHeadlights();
    initScrollProgress();
    initMenu();
    initHeroMotion();
    initCardTilt();
    initLightShowroom();
    initMagazine();
    initShareLinks();
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
