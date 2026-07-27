@php
    $flash = session('status');
@endphp

<div
    aria-live="polite"
    aria-atomic="true"
    style="position:fixed;top:1rem;inset-inline-end:1rem;z-index:60;display:flex;flex-direction:column;gap:.5rem;max-width:22rem;"
    x-data="{
        toasts: [],
        push(detail) {
            const id = Date.now() + Math.random();
            this.toasts.push({
                id,
                type: detail.type || 'success',
                message: detail.message || '',
            });
            setTimeout(() => this.remove(id), detail.timeout || 4000);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        },
        color(type) {
            return {
                success: '#16a34a',
                danger: '#dc2626',
                error: '#dc2626',
                warning: '#d97706',
                info: '#2563eb',
            }[type] || '#2563eb';
        },
    }"
    x-on:notify.window="push($event.detail)"
    @if ($flash) x-init="push({ type: 'success', message: @js($flash) })" @endif
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            role="status"
            style="display:flex;align-items:flex-start;gap:.6rem;padding:.75rem 1rem;border-radius:.5rem;background:#ffffff;color:#111827;box-shadow:0 8px 24px rgba(0,0,0,.15);border-inline-start:4px solid;"
            :style="`border-inline-start-color:${color(toast.type)}`"
        >
            <span
                style="flex-shrink:0;width:.6rem;height:.6rem;border-radius:9999px;margin-top:.35rem;"
                :style="`background:${color(toast.type)}`"
            ></span>
            <span style="flex:1;font-size:.875rem;line-height:1.3;" x-text="toast.message"></span>
            <button
                type="button"
                x-on:click="remove(toast.id)"
                aria-label="Dismiss"
                style="flex-shrink:0;border:0;background:transparent;cursor:pointer;color:#6b7280;font-size:1rem;line-height:1;"
            >&times;</button>
        </div>
    </template>
</div>
