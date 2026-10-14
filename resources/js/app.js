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
        const retry = root.querySelector('[data-photo-retry]');
        if (!input || !dropzone || !preview) return;

        root.dataset.mcPhotoUploaderReady = 'true';
        const maxFiles = Number(root.dataset.maxFiles || 12);
        const maxBytes = Number(root.dataset.maxSizeKb || 5120) * 1024;
        const accepted = (input.accept || '').split(',').map((type) => type.trim().toLowerCase()).filter(Boolean);
        let files = [];
        let internalChange = false;
        let totalBytes = 0;

        const fileKey = (file) => file.name + ':' + file.size + ':' + file.lastModified;
        const isAccepted = (file) => {
            if (!accepted.length || accepted.includes('*/*')) return true;
            const extension = '.' + (file.name.split('.').pop()?.toLowerCase() || '');
            return accepted.includes(file.type.toLowerCase()) || accepted.includes(extension);
        };
        const formatSize = (bytes) => (bytes / 1024 / 1024).toFixed(1) + ' MB';

        const syncInput = (notify = true) => {
            const transfer = new DataTransfer();
            files.forEach(({file}) => transfer.items.add(file));
            input.files = transfer.files;
            if (notify) {
                internalChange = true;
                input.dispatchEvent(new Event('change', {bubbles: true}));
                window.queueMicrotask(() => { internalChange = false; });
            }
        };

        const render = () => {
            preview.replaceChildren();
            totalBytes = files.reduce((sum, item) => sum + item.file.size, 0);
            files.forEach((item, index) => {
                const card = document.createElement('figure');
                card.className = 'mc-photo-uploader__item is-' + item.state;
                card.setAttribute('role', 'listitem');
                const image = document.createElement('img');
                image.alt = item.file.name;
                image.loading = 'lazy';
                image.src = item.previewUrl;
                const details = document.createElement('figcaption');
                details.textContent = item.file.name;
                const meta = document.createElement('p');
                meta.className = 'mc-photo-uploader__meta';
                meta.textContent = item.error || (item.state === 'done' ? 'Enviada' : item.state === 'uploading' ? item.progress + '%' : formatSize(item.file.size));
                const progress = document.createElement('progress');
                progress.className = 'mc-photo-uploader__progress';
                progress.max = 100;
                progress.value = item.progress;
                progress.setAttribute('aria-label', 'Progresso de ' + item.file.name);
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'mc-photo-uploader__remove';
                remove.setAttribute('aria-label', 'Remover ' + item.file.name);
                remove.textContent = 'Remover';
                remove.disabled = item.state === 'uploading';
                remove.addEventListener('click', () => {
                    URL.revokeObjectURL(item.previewUrl);
                    files.splice(index, 1);
                    syncInput();
                    render();
                });
                card.append(image, details, progress, meta, remove);
                preview.append(card);
            });
            if (count) count.textContent = files.length ? files.length + ' de ' + maxFiles + ' fotos' : 'Nenhuma foto selecionada';
            preview.setAttribute('aria-busy', String(files.some((item) => item.state === 'uploading')));
            if (retry) retry.classList.toggle('hidden', !files.some((item) => item.state === 'error'));
        };

        const updateProgress = (value) => {
            const progress = clamp(Number(value) || 0, 0, 100) / 100;
            let consumed = totalBytes * progress;
            files.forEach((item) => {
                if (item.state === 'error') return;
                item.progress = Math.round(clamp(consumed / Math.max(item.file.size, 1), 0, 1) * 100);
                item.state = item.progress >= 100 ? 'done' : 'uploading';
                consumed -= item.file.size;
            });
            render();
        };

        const addFiles = (incoming) => {
            const rejected = [];
            const existing = new Set(files.map(({file}) => fileKey(file)));
            [...incoming].forEach((file) => {
                if (!isAccepted(file)) { rejected.push(file.name + ': formato não permitido'); return; }
                if (file.size > maxBytes) { rejected.push(file.name + ': ultrapassa ' + formatSize(maxBytes)); return; }
                if (existing.has(fileKey(file))) return;
                if (files.length >= maxFiles) { rejected.push(file.name + ': limite de ' + maxFiles + ' fotos'); return; }
                const item = {file, state: 'ready', progress: 0, error: '', previewUrl: URL.createObjectURL(file)};
                files.push(item);
                existing.add(fileKey(file));
            });
            if (status) {
                status.classList.toggle('text-red-600', rejected.length > 0);
                status.textContent = rejected.length ? rejected.join(' · ') : (files.length ? 'Revise as miniaturas antes de salvar.' : '');
            }
            syncInput();
            render();
        };

        const markAll = (state, message = '') => {
            files.forEach((item) => {
                if (item.state !== 'error') {
                    item.state = state;
                    item.error = message;
                    if (state === 'done') item.progress = 100;
                }
            });
            render();
        };

        dropzone.addEventListener('click', () => input.click());
        dropzone.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); input.click(); }
        });
        input.addEventListener('change', (event) => { if (!internalChange) addFiles(event.target.files); });
        input.addEventListener('livewire-upload-start', () => {
            files.forEach((item) => { if (item.state !== 'error') { item.state = 'uploading'; item.progress = 0; } });
            if (status) status.textContent = 'Enviando fotos…';
            render();
        });
        input.addEventListener('livewire-upload-progress', (event) => updateProgress(event.detail?.progress));
        input.addEventListener('livewire-upload-finish', () => {
            markAll('done');
            root.classList.remove('is-uploading');
            if (status) status.textContent = 'Upload concluído. Salvando…';
        });
        input.addEventListener('livewire-upload-error', () => {
            files.forEach((item) => { if (item.state !== 'done') { item.state = 'error'; item.error = 'Falhou. Você pode tentar novamente.'; } });
            root.classList.remove('is-uploading');
            if (status) status.textContent = 'Algumas fotos falharam. Tente novamente sem perder as outras.';
            render();
        });
        ['dragenter', 'dragover'].forEach((eventName) => dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.add('is-dragging');
        }));
        ['dragleave', 'drop'].forEach((eventName) => dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.remove('is-dragging');
        }));
        dropzone.addEventListener('drop', (event) => addFiles(event.dataTransfer.files));
        retry?.addEventListener('click', () => {
            files.forEach((item) => { if (item.state === 'error') { item.state = 'ready'; item.error = ''; item.progress = 0; } });
            if (status) status.textContent = 'Pronto para tentar novamente.';
            syncInput();
            render();
        });
        input.form?.addEventListener('submit', () => {
            root.classList.add('is-uploading');
            files.forEach((item) => { if (item.state !== 'done') item.state = 'uploading'; });
            if (status && files.length) status.textContent = 'Enviando ' + files.length + ' fotos…';
            render();
        });
        render();
    });
};

const initPhotoLibraries = () => {
    document.querySelectorAll('[data-photo-library]').forEach((library) => {
        if (library.dataset.mcPhotoLibraryReady) return;
        library.dataset.mcPhotoLibraryReady = 'true';
        let dragged = null;
        const saveOrder = () => {
            const ids = [...library.querySelectorAll('[data-photo-card]')].map((card) => Number(card.dataset.photoId));
            const componentRoot = library.closest('[wire\\:id]');
            const component = componentRoot && window.Livewire?.find(componentRoot.getAttribute('wire:id'));
            component?.call('reorderPhotos', ids);
        };
        library.querySelectorAll('[data-photo-card]').forEach((card) => {
            card.addEventListener('dragstart', () => {
                dragged = card;
                card.classList.add('is-dragging');
            });
            card.addEventListener('dragend', () => {
                dragged?.classList.remove('is-dragging');
                dragged = null;
            });
            card.addEventListener('dragover', (event) => {
                event.preventDefault();
                if (!dragged || dragged === card) return;
                const rect = card.getBoundingClientRect();
                const after = event.clientY > rect.top + rect.height / 2;
                library.insertBefore(dragged, after ? card.nextSibling : card);
            });
            card.addEventListener('drop', (event) => {
                event.preventDefault();
                saveOrder();
            });
        });
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
            const status = control.querySelector('[data-theme-toggle-status]');
            if (status && status.textContent !== state) status.replaceChildren(document.createTextNode(state));
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
    hero.classList.add('is-visible');
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
        const modeStorageKey = 'autoverse-magazine-mode-v2';
        let mode = window.localStorage.getItem(modeStorageKey) || root.dataset.avDefaultMode || 'magazine';
        let animationTimer;
        let touchStartX = null;

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
            window.localStorage.setItem(modeStorageKey, mode);
            if (mode === 'reading') reading.querySelector('h2')?.focus?.({preventScroll: true});
        };

        const readingPages = [...root.querySelectorAll('[data-av-reading-page]')];
        if (readingPages.length) {
            if (reduceMotion.matches || !('IntersectionObserver' in window)) {
                readingPages.forEach((page) => page.classList.add('is-visible'));
            } else {
                const observer = new IntersectionObserver((entries, instance) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        entry.target.classList.add('is-visible');
                        instance.unobserve(entry.target);
                    });
                }, { threshold: 0.16 });
                readingPages.forEach((page) => observer.observe(page));
            }
        }

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
        book.addEventListener('touchstart', (event) => {
            if (event.touches.length === 1) touchStartX = event.touches[0].clientX;
        }, { passive: true });
        book.addEventListener('touchend', (event) => {
            if (touchStartX === null || !event.changedTouches.length) return;
            const distance = event.changedTouches[0].clientX - touchStartX;
            touchStartX = null;
            if (Math.abs(distance) < 44) return;
            goTo(distance < 0 ? index + 1 : index - 1);
        }, { passive: true });
        window.addEventListener('resize', () => sync(), {passive: true});
        setMode(mode);
        sync();
    });
};

const initMagazineEditor = () => {
    document.querySelectorAll('[data-magazine-order]').forEach((list) => {
        if (list.dataset.magazineOrderReady) return;
        list.dataset.magazineOrderReady = 'true';
        let dragged = null;

        const move = (item, direction) => {
            const sibling = direction === 'up' ? item.previousElementSibling : item.nextElementSibling;
            if (!sibling) return;
            list.insertBefore(item, direction === 'up' ? sibling : sibling.nextElementSibling);
        };

        list.querySelectorAll('[data-magazine-move]').forEach((button) => {
            button.addEventListener('click', () => move(button.closest('[data-magazine-chapter]'), button.dataset.magazineMove));
        });
        list.querySelectorAll('[data-magazine-chapter]').forEach((item) => {
            item.addEventListener('dragstart', () => {
                dragged = item;
                item.classList.add('opacity-50');
            });
            item.addEventListener('dragend', () => {
                dragged = null;
                item.classList.remove('opacity-50');
            });
            item.addEventListener('dragover', (event) => {
                event.preventDefault();
                if (!dragged || dragged === item) return;
                const bounds = item.getBoundingClientRect();
                list.insertBefore(dragged, event.clientY < bounds.top + bounds.height / 2 ? item : item.nextElementSibling);
            });
        });
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
    initPhotoLibraries();
    initSectionTabs();
    initThemeHeadlights();
    initScrollProgress();
    initMenu();
    initHeroMotion();
    initCardTilt();
    initLightShowroom();
    initMagazine();
    initMagazineEditor();
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
    let observerFrame = 0;
    const scheduleMotionInit = () => {
        if (observerFrame) return;
        observerFrame = window.requestAnimationFrame(() => {
            observerFrame = 0;
            initMotion();
        });
    };
    const observer = new MutationObserver(scheduleMotionInit);
    observer.observe(document.body, {childList: true, subtree: true});
});
