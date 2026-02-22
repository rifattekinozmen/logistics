<!-- Toast Container -->
<div id="toast-container" class="position-fixed" style="top: 20px; right: 20px; z-index: 1050;">
</div>

<!-- Toast Template -->
<template id="toast-template">
    <div class="toast fade show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
        <div class="toast-header">
            <span class="material-symbols-outlined toast-icon me-2" style="font-size: 1.25rem;"></span>
            <strong class="toast-title me-auto"></strong>
            <small class="text-muted toast-time"></small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Kapat"></button>
        </div>
        <div class="toast-body">
            <span class="toast-message"></span>
        </div>
    </div>
</template>

@push('styles')
<style>
    .toast {
        min-width: 300px;
        max-width: 400px;
        margin-bottom: 10px;
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(0, 0, 0, 0.1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border-radius: 8px;
        animation: toastSlideInRight 0.3s ease-out;
    }
    .toast.toast-success { border-left: 4px solid var(--bs-success, #2D8B6F); }
    .toast.toast-error { border-left: 4px solid var(--bs-danger, #C41E5A); }
    .toast.toast-warning { border-left: 4px solid var(--bs-warning, #ffc107); }
    .toast.toast-info { border-left: 4px solid var(--bs-info, #3775A8); }
    .toast-icon.success { color: var(--bs-success, #2D8B6F) !important; }
    .toast-icon.error { color: var(--bs-danger, #C41E5A) !important; }
    .toast-icon.warning { color: var(--bs-warning, #ffc107) !important; }
    .toast-icon.info { color: var(--bs-info, #3775A8) !important; }
    @keyframes toastSlideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @media (max-width: 576px) {
        #toast-container { top: 10px; right: 10px; left: 10px; }
        .toast { min-width: auto; max-width: none; width: 100%; }
    }
</style>
@endpush

@push('scripts')
<script>
(function() {
    const iconMap = { success: 'check_circle', error: 'error', warning: 'warning', info: 'info' };
    const titleMap = { success: 'Başarılı', error: 'Hata', warning: 'Uyarı', info: 'Bilgi' };

    window.toastSystem = {
        container: null,
        template: null,
        init() {
            this.container = document.getElementById('toast-container');
            this.template = document.getElementById('toast-template');
        },
        show(message, type = 'info', title = null, options = {}) {
            if (!this.container || !this.template) this.init();
            const config = { autohide: true, delay: 5000, showTime: true, ...options };
            const toastEl = this.template.content.cloneNode(true);
            const toast = toastEl.querySelector('.toast');
            toast.classList.add('toast-' + type);
            const iconEl = toast.querySelector('.toast-icon');
            iconEl.classList.add(type);
            iconEl.textContent = iconMap[type] || 'info';
            toast.querySelector('.toast-title').textContent = title || titleMap[type] || 'Bildirim';
            toast.querySelector('.toast-message').textContent = message;
            const timeEl = toast.querySelector('.toast-time');
            if (config.showTime) {
                timeEl.textContent = new Date().toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
            } else {
                timeEl.style.display = 'none';
            }
            toast.setAttribute('data-bs-autohide', config.autohide ? 'true' : 'false');
            toast.setAttribute('data-bs-delay', config.delay);
            this.container.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            toast.addEventListener('hidden.bs.toast', () => toast.remove());
            return bsToast;
        },
        success(m, t, o) { return this.show(m, 'success', t, o); },
        error(m, t, o) { return this.show(m, 'error', t, o); },
        warning(m, t, o) { return this.show(m, 'warning', t, o); },
        info(m, t, o) { return this.show(m, 'info', t, o); },
        showSessionMessages() {
            @if (session('success'))
            this.success(@json(session('success')));
            @endif
            @if (session('error'))
            this.error(@json(session('error')));
            @endif
            @if (session('warning'))
            this.warning(@json(session('warning')));
            @endif
            @if (session('info'))
            this.info(@json(session('info')));
            @endif
        }
    };
    document.addEventListener('DOMContentLoaded', function() {
        window.toastSystem.init();
        window.toastSystem.showSessionMessages();
    });
    window.showToast = window.toastSystem.show.bind(window.toastSystem);
    window.showSuccess = window.toastSystem.success.bind(window.toastSystem);
    window.showError = window.toastSystem.error.bind(window.toastSystem);
    window.showWarning = window.toastSystem.warning.bind(window.toastSystem);
    window.showInfo = window.toastSystem.info.bind(window.toastSystem);
})();
</script>
@endpush
