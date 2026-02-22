<!-- Global silme onay modali -->
<div class="modal fade" id="globalDeleteModal" tabindex="-1" aria-labelledby="globalDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title text-danger" id="globalDeleteModalLabel">
                    <span class="material-symbols-outlined me-2" style="font-size: 1.25rem;">warning</span>
                    Silme Onayı
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">
                    <span id="globalDeleteItemName" class="fw-semibold"></span> kalıcı olarak silinecek.
                </p>
                <small class="text-muted">Bu işlem geri alınamaz.</small>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-danger" id="globalConfirmDelete">
                    <span class="material-symbols-outlined me-1" style="font-size: 1rem; vertical-align: middle;">delete</span>
                    Sil
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    let current = null;

    function ensureModal() {
        const el = document.getElementById('globalDeleteModal');
        if (!el) return null;
        return bootstrap.Modal.getOrCreateInstance(el);
    }

    function setBusy(isBusy) {
        const btn = document.getElementById('globalConfirmDelete');
        if (!btn) return;
        btn.disabled = isBusy;
        btn.innerHTML = isBusy
            ? '<span class="spinner-border spinner-border-sm me-1"></span>Siliniyor...'
            : '<span class="material-symbols-outlined me-1" style="font-size: 1rem; vertical-align: middle;">delete</span> Sil';
    }

    window.showDeleteConfirm = function(options) {
        const name = options?.name || '';
        const id = options?.id;
        const nameEl = document.getElementById('globalDeleteItemName');
        if (nameEl) {
            nameEl.textContent = `"${name}"`;
        }

        current = {
            id,
            name,
            buildUrl: typeof options?.buildUrl === 'function' ? options.buildUrl : null,
            onConfirm: typeof options?.onConfirm === 'function' ? options.onConfirm : null
        };

        const modal = ensureModal();
        modal && modal.show();
    };

    window.datatableDeleteModal = function(id, name) {
        window.showDeleteConfirm({
            id,
            name,
            onConfirm: async function() {
                if (window.dataTable) {
                    window.dataTable.itemToDelete = id;
                    await window.dataTable.confirmDelete();
                }
            }
        });
    };

    window.showDeleteModal = function(id, name, endpointBase) {
        window.showDeleteConfirm({
            id,
            name,
            buildUrl: function(theId) {
                if (!endpointBase) return '';
                if (endpointBase.includes(':id')) return endpointBase.replace(':id', theId);
                return endpointBase.endsWith('/') ? endpointBase + theId : endpointBase + '/' + theId;
            }
        });
    };

    document.addEventListener('click', async function(e) {
        if (e.target && e.target.id === 'globalConfirmDelete') {
            if (!current) return;
            try {
                setBusy(true);
                if (current.onConfirm) {
                    await current.onConfirm();
                } else if (current.buildUrl) {
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
                    const res = await fetch(current.buildUrl(current.id), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.ok) {
                        if (window.dataTable?.loadData) {
                            window.dataTable.loadData();
                        } else {
                            if (window.showSuccess) window.showSuccess(data.message || 'Kayıt silindi', 'Başarılı');
                            setTimeout(function () { location.reload(); }, 800);
                        }
                    } else {
                        const msg = data.message || 'Silme işlemi başarısız';
                        if (window.showError) window.showError(msg, 'Hata');
                        else alert(msg);
                    }
                }
                const modalEl = document.getElementById('globalDeleteModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal && modal.hide();
            } catch (err) {
                console.error('Global delete error:', err);
                if (window.showError) window.showError('Silme sırasında hata oluştu');
                else alert('Silme sırasında hata oluştu');
            } finally {
                setBusy(false);
                current = null;
            }
        }
    });
})();
</script>
@endpush
