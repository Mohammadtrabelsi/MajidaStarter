<button
    type="button"
    x-data="{
        dark: document.documentElement.classList.contains('dark'),
        toggle() {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            try { localStorage.setItem('theme', this.dark ? 'dark' : 'light'); } catch (e) {}
        }
    }"
    @click="toggle()"
    :aria-label="dark ? 'Switch to light mode' : 'Switch to dark mode'"
    class="icon-btn"
    title="Toggle theme"
>
    <svg x-show="!dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z"></path></svg>
    <svg x-show="dark" class="ms-hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="4"></circle><path stroke-linecap="round" d="M12 2v2m0 16v2M4 12H2m20 0h-2M5.6 5.6L4.2 4.2m15.6 15.6l-1.4-1.4M18.4 5.6l1.4-1.4M4.2 19.8l1.4-1.4"></path></svg>
</button>
