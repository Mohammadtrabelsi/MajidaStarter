/*
 * Front-end behaviour for the app, written in plain vanilla JavaScript.
 *
 * This file replaces the small pieces of interactivity that used to be
 * expressed with Alpine.js directives (x-data / x-show / @click / …) in the
 * Blade views. Everything here is driven by `data-*` attributes so the markup
 * stays declarative and no component framework is required.
 *
 * Design notes:
 *  - Click behaviour uses event delegation on `document`, so it keeps working
 *    across Livewire DOM morphs and `wire:navigate` SPA navigations without
 *    needing to be re-bound.
 *  - Server-dispatched Livewire events (`$this->dispatch(...)`) surface as
 *    native `window` CustomEvents, which we listen for directly.
 *  - Per-page initialisation (auto-hiding flash notices, seeding a flash
 *    toast) runs on first load and again after every `livewire:navigated`.
 */

/* ------------------------------------------------------------------ *
 * Dropdown menus (user menu, locale switcher)
 *
 *   <div data-dropdown>
 *     <button data-dropdown-toggle>…</button>
 *     <div data-dropdown-menu>…</div>
 *   </div>
 * ------------------------------------------------------------------ */
function closeAllDropdowns(except) {
    document.querySelectorAll('[data-dropdown].is-open').forEach((el) => {
        if (el !== except) {
            el.classList.remove('is-open');
        }
    });
}

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-dropdown-toggle]');

    if (toggle) {
        const dropdown = toggle.closest('[data-dropdown]');
        const willOpen = !dropdown.classList.contains('is-open');
        closeAllDropdowns(dropdown);
        dropdown.classList.toggle('is-open', willOpen);
        return;
    }

    // A click anywhere that is not inside an open menu closes the menus.
    if (!event.target.closest('[data-dropdown-menu]')) {
        closeAllDropdowns();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeAllDropdowns();
    }
});

/* ------------------------------------------------------------------ *
 * Mobile sidebar
 *
 *   <div class="shell" data-sidebar> … burger: data-sidebar-open
 *   overlay: data-sidebar-close … aside: .shell-sidebar
 * ------------------------------------------------------------------ */
function setSidebar(open) {
    const sidebar = document.querySelector('.shell-sidebar');
    const overlay = document.querySelector('[data-sidebar-overlay]');
    if (sidebar) sidebar.classList.toggle('open', open);
    if (overlay) overlay.classList.toggle('is-visible', open);
}

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-sidebar-open]')) {
        setSidebar(true);
    } else if (event.target.closest('[data-sidebar-close]')) {
        setSidebar(false);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setSidebar(false);
});

// Sidebar is a mobile-only overlay; always reset it after navigation.
document.addEventListener('livewire:navigated', () => setSidebar(false));

/* ------------------------------------------------------------------ *
 * Theme toggle (light / dark)
 *
 *   <button data-theme-toggle> … icons: .theme-icon-light / .theme-icon-dark
 *
 * Icon visibility is handled entirely in CSS based on the `dark` class on
 * <html>; this handler only flips that class, persists the choice, and keeps
 * the accessible label in sync.
 * ------------------------------------------------------------------ */
function syncThemeLabels() {
    const isDark = document.documentElement.classList.contains('dark');
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.setAttribute(
            'aria-label',
            isDark ? 'Switch to light mode' : 'Switch to dark mode'
        );
    });
}

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-theme-toggle]');
    if (!toggle) return;

    const isDark = document.documentElement.classList.toggle('dark');
    try {
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    } catch (e) {
        /* storage unavailable — theme still applies for this page */
    }
    syncThemeLabels();
});

document.addEventListener('livewire:navigated', syncThemeLabels);

/* ------------------------------------------------------------------ *
 * Accordion / disclosure rows (activity log)
 *
 *   <div data-accordion>
 *     <button data-accordion-toggle="{id}">
 *       <svg data-accordion-chevron="{id}">
 *     <div data-accordion-panel="{id}">…</div>
 *
 * Single-open behaviour: opening one row closes the others.
 * ------------------------------------------------------------------ */
document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-accordion-toggle]');
    if (!toggle) return;

    const accordion = toggle.closest('[data-accordion]');
    if (!accordion) return;

    const id = toggle.getAttribute('data-accordion-toggle');
    const panel = accordion.querySelector(`[data-accordion-panel="${id}"]`);
    const isOpen = panel ? panel.classList.contains('is-open') : false;

    accordion.querySelectorAll('[data-accordion-panel].is-open').forEach((p) => {
        p.classList.remove('is-open');
    });
    accordion.querySelectorAll('[data-accordion-chevron].is-open').forEach((c) => {
        c.classList.remove('is-open');
    });

    if (panel && !isOpen) {
        panel.classList.add('is-open');
        const chevron = accordion.querySelector(`[data-accordion-chevron="${id}"]`);
        if (chevron) chevron.classList.add('is-open');
    }
});

/* ------------------------------------------------------------------ *
 * Transient "saved" confirmations
 *
 *   <div data-show-on="profile-updated" data-show-duration="3000">…</div>
 *
 * Shown when the matching Livewire browser event fires, then hidden again
 * after the given duration (default 3s).
 * ------------------------------------------------------------------ */
function wireShowOnListeners() {
    const events = new Set();
    document.querySelectorAll('[data-show-on]').forEach((el) => {
        events.add(el.getAttribute('data-show-on'));
    });

    events.forEach((name) => {
        if (wireShowOnListeners.bound.has(name)) return;
        wireShowOnListeners.bound.add(name);

        window.addEventListener(name, () => {
            document
                .querySelectorAll(`[data-show-on="${name}"]`)
                .forEach((el) => {
                    const duration = parseInt(el.getAttribute('data-show-duration') || '3000', 10);
                    el.classList.add('is-visible');
                    if (el._hideTimer) clearTimeout(el._hideTimer);
                    el._hideTimer = setTimeout(() => el.classList.remove('is-visible'), duration);
                });
        });
    });
}
wireShowOnListeners.bound = new Set();

/* ------------------------------------------------------------------ *
 * Auto-hiding flash notices
 *
 *   <div data-autohide="3000">…</div>
 * ------------------------------------------------------------------ */
function wireAutohide() {
    document.querySelectorAll('[data-autohide]').forEach((el) => {
        if (el.dataset.autohideBound) return;
        el.dataset.autohideBound = '1';
        const duration = parseInt(el.getAttribute('data-autohide') || '3000', 10);
        setTimeout(() => {
            el.style.display = 'none';
        }, duration);
    });
}

/* ------------------------------------------------------------------ *
 * Toast hub
 *
 *   <div data-toast-hub data-toast-flash="…optional flash message…"></div>
 *
 * Toasts are pushed by the `notify` Livewire browser event
 * ({ type, message }) and auto-dismiss after their timeout.
 * ------------------------------------------------------------------ */
const TOAST_COLORS = {
    success: '#16a34a',
    danger: '#dc2626',
    error: '#dc2626',
    warning: '#d97706',
    info: '#2563eb',
};

function pushToast(detail) {
    const hub = document.querySelector('[data-toast-hub]');
    if (!hub) return;

    const type = (detail && detail.type) || 'success';
    const message = (detail && detail.message) || '';
    const timeout = (detail && detail.timeout) || 4000;
    const color = TOAST_COLORS[type] || TOAST_COLORS.info;

    const toast = document.createElement('div');
    toast.className = 'ms-toast';
    toast.setAttribute('role', 'status');
    toast.style.borderInlineStartColor = color;

    const dot = document.createElement('span');
    dot.className = 'ms-toast-dot';
    dot.style.background = color;

    const text = document.createElement('span');
    text.className = 'ms-toast-message';
    text.textContent = message;

    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'ms-toast-close';
    close.setAttribute('aria-label', 'Dismiss');
    close.innerHTML = '&times;';
    close.addEventListener('click', () => removeToast(toast));

    toast.append(dot, text, close);
    hub.appendChild(toast);

    // Trigger the enter transition on the next frame.
    requestAnimationFrame(() => toast.classList.add('is-visible'));

    setTimeout(() => removeToast(toast), timeout);
}

function removeToast(toast) {
    if (!toast || toast._removing) return;
    toast._removing = true;
    toast.classList.remove('is-visible');
    setTimeout(() => toast.remove(), 200);
}

window.addEventListener('notify', (event) => pushToast(event.detail || {}));

function seedFlashToast() {
    const hub = document.querySelector('[data-toast-hub]');
    if (!hub || hub.dataset.flashSeeded) return;
    hub.dataset.flashSeeded = '1';
    const flash = hub.getAttribute('data-toast-flash');
    if (flash) {
        pushToast({ type: 'success', message: flash });
    }
}

/* ------------------------------------------------------------------ *
 * Per-page initialisation
 * ------------------------------------------------------------------ */
function init() {
    wireShowOnListeners();
    wireAutohide();
    seedFlashToast();
    syncThemeLabels();
}

document.addEventListener('DOMContentLoaded', init);
document.addEventListener('livewire:navigated', () => {
    // A fresh toast hub is rendered on navigation; allow it to seed again.
    const hub = document.querySelector('[data-toast-hub]');
    if (hub) delete hub.dataset.flashSeeded;
    init();
});
