@props([
    'exportRoute' => '',
    'templateRoute' => '',
    'importRoute' => '',
    'modalId' => 'excelModal',
    'maxFileSize' => '2MB',
    'mode' => 'inline',
])

@if ($mode === 'dropdown')
    <div class="btn-group excel-actions" role="group">
        <x-ui.button id="{{ $modalId }}Toggle" variant="outline-success" size="sm" class="dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" icon="table_chart">
            Excel
        </x-ui.button>
        <ul class="dropdown-menu shadow-sm" aria-labelledby="{{ $modalId }}Toggle">
            @if ($exportRoute)
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ $exportRoute }}">
                        <span class="material-symbols-outlined text-success" style="font-size: 1rem;">download</span>
                        Dışa Aktar
                    </a>
                </li>
            @endif
            @if ($templateRoute)
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ $templateRoute }}">
                        <span class="material-symbols-outlined text-info" style="font-size: 1rem;">file_download</span>
                        Şablon İndir
                    </a>
                </li>
            @endif
            @if ($exportRoute || $templateRoute)
                <li><hr class="dropdown-divider"></li>
            @endif
            @if ($importRoute)
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="#{{ $modalId }}" role="button" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                        <span class="material-symbols-outlined text-primary" style="font-size: 1rem;">upload</span>
                        İçe Aktar
                    </a>
                </li>
            @endif
        </ul>
    </div>
@else
    <div class="btn-group excel-actions" role="group">
        @if ($exportRoute)
            <x-ui.button as="a" href="{{ $exportRoute }}" variant="outline-success" size="sm" icon="download">Dışa Aktar</x-ui.button>
        @endif
        @if ($templateRoute)
            <x-ui.button as="a" href="{{ $templateRoute }}" variant="outline-info" size="sm" icon="file_download">Şablon İndir</x-ui.button>
        @endif
        @if ($importRoute)
            <x-ui.button variant="primary" size="sm" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}" icon="upload">İçe Aktar</x-ui.button>
        @endif
    </div>
@endif

@if ($importRoute)
    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-medium">
                        <span class="material-symbols-outlined me-2 text-primary" style="font-size: 1.25rem;">upload_file</span>
                        Excel/CSV İçe Aktar
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="{{ $modalId }}Form" action="{{ $importRoute }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body pt-0">
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-2" for="{{ $modalId }}File">Dosya Seçin</label>
                            <input type="file" class="form-control" id="{{ $modalId }}File" name="import_file" accept=".csv,.xlsx,.xls" required>
                            <div class="form-text small">
                                <div class="d-flex justify-content-between mt-1">
                                    <span><code>.csv</code>, <code>.xlsx</code>, <code>.xls</code> dosyaları</span>
                                    <span class="text-muted">Max: {{ $maxFileSize }}</span>
                                </div>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="alert alert-warning border-0 mb-3">
                            <div class="d-flex">
                                <span class="material-symbols-outlined text-warning me-2 mt-1 flex-shrink-0" style="font-size: 1.25rem;">info</span>
                                <div class="small">
                                    <div class="fw-medium mb-2">Önemli Notlar:</div>
                                    <ul class="mb-0 ps-3">
                                        <li>Dosya boyutu maksimum {{ $maxFileSize }} olabilir</li>
                                        <li>İlk satır başlık satırı olarak kabul edilir</li>
                                        <li>CSV dosyaları için ayraç olarak ; (noktalı virgül) kullanılmalıdır</li>
                                        <li>Veriler UTF-8 kodlamasında olmalıdır</li>
                                        <li>Excel dosyaları için .xlsx veya .xls formatı kullanılabilir</li>
                                        <li>Boş satırlar otomatik olarak atlanır</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        @if ($templateRoute)
                            <div class="text-center mb-3">
                                <x-ui.button as="a" href="{{ $templateRoute }}" variant="link" size="sm" class="text-decoration-none" icon="download">
                                    Şablon Dosyayı İndir
                                </x-ui.button>
                            </div>
                        @endif

                        <div id="{{ $modalId }}Progress" class="d-none">
                            <div class="text-center py-3">
                                <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                                <div class="small text-muted">Dosya işleniyor...</div>
                            </div>
                        </div>

                        <div id="{{ $modalId }}Results" class="d-none">
                            <div class="alert mb-0" id="{{ $modalId }}Alert">
                                <div id="{{ $modalId }}Message"></div>
                                <div id="{{ $modalId }}Stats" class="d-none mt-3">
                                    <div class="row g-2 text-center">
                                        <div class="col-4">
                                            <div class="small text-muted">İçe Aktarılan</div>
                                            <div class="fw-medium text-success" id="{{ $modalId }}Imported">0</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="small text-muted">Güncellenen</div>
                                            <div class="fw-medium text-warning" id="{{ $modalId }}Updated">0</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="small text-muted">Başarısız</div>
                                            <div class="fw-medium text-danger" id="{{ $modalId }}Failed">0</div>
                                        </div>
                                    </div>
                                </div>
                                <div id="{{ $modalId }}Errors" class="d-none mt-3">
                                    <details>
                                        <summary class="small fw-medium text-danger cursor-pointer">
                                            <span class="material-symbols-outlined align-middle" style="font-size: 0.875rem;">warning</span>
                                            Hatalar (<span id="{{ $modalId }}ErrorCount">0</span>)
                                        </summary>
                                        <div class="mt-2 small">
                                            <ul id="{{ $modalId }}ErrorList" class="mb-0 ps-3"></ul>
                                        </div>
                                    </details>
                                </div>
                                <div id="{{ $modalId }}ReloadBtn" class="d-none mt-3 text-center">
                                    <x-ui.button variant="outline-primary" size="sm" onclick="location.reload()" icon="refresh">
                                        Sayfayı Yenile
                                    </x-ui.button>
                                    <div class="small text-muted mt-1">Değişiklikleri görmek için sayfayı yenileyin</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <x-ui.button variant="secondary" size="sm" data-bs-dismiss="modal">İptal</x-ui.button>
                        <x-ui.button as="submit" variant="primary" size="sm" id="{{ $modalId }}Btn" icon="upload">İçe Aktar</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function() {
        function initExcelModal() {
            const modalId = '{{ $modalId }}';
            const form = document.getElementById(modalId + 'Form');
            const fileInput = document.getElementById(modalId + 'File');
            const submitBtn = document.getElementById(modalId + 'Btn');
            const progressDiv = document.getElementById(modalId + 'Progress');
            const resultsDiv = document.getElementById(modalId + 'Results');

            if (!form) return;

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (!fileInput.files.length) {
                    fileInput.classList.add('is-invalid');
                    return;
                }
                submitBtn.disabled = true;
                progressDiv.classList.remove('d-none');
                resultsDiv.classList.add('d-none');

                const formData = new FormData();
                formData.append('import_file', fileInput.files[0]);
                const csrf = document.querySelector('meta[name="csrf-token"]');
                if (csrf) formData.append('_token', csrf.getAttribute('content'));

                try {
                    const res = await fetch('{{ $importRoute }}', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf?.getAttribute('content') || '' }
                    });
                    const data = await res.json();
                    progressDiv.classList.add('d-none');
                    resultsDiv.classList.remove('d-none');

                    const alertEl = document.getElementById(modalId + 'Alert');
                    const msgEl = document.getElementById(modalId + 'Message');
                    alertEl.className = 'alert mb-0 ' + (data.success ? 'alert-success' : 'alert-danger');
                    msgEl.innerHTML = data.message;

                    if (data.success) {
                        if (data.imported !== undefined) {
                            document.getElementById(modalId + 'Stats').classList.remove('d-none');
                            document.getElementById(modalId + 'Imported').textContent = data.imported;
                            document.getElementById(modalId + 'Updated').textContent = data.updated || 0;
                            document.getElementById(modalId + 'Failed').textContent = data.total_errors || 0;
                        }
                        if (data.errors && data.errors.length) {
                            document.getElementById(modalId + 'Errors').classList.remove('d-none');
                            document.getElementById(modalId + 'ErrorCount').textContent = data.errors.length;
                            const list = document.getElementById(modalId + 'ErrorList');
                            list.innerHTML = '';
                            data.errors.forEach(err => { const li = document.createElement('li'); li.textContent = err; list.appendChild(li); });
                        }
                        document.getElementById(modalId + 'ReloadBtn').classList.remove('d-none');
                        form.reset();
                        fileInput.classList.remove('is-invalid');
                    } else if (data.errors && typeof data.errors === 'object') {
                        const list = document.getElementById(modalId + 'ErrorList');
                        list.innerHTML = '';
                        Object.values(data.errors).flat().forEach(err => { const li = document.createElement('li'); li.textContent = err; list.appendChild(li); });
                        document.getElementById(modalId + 'Errors').classList.remove('d-none');
                    }
                } catch (err) {
                    progressDiv.classList.add('d-none');
                    resultsDiv.classList.remove('d-none');
                    document.getElementById(modalId + 'Alert').className = 'alert alert-danger mb-0';
                    document.getElementById(modalId + 'Message').textContent = err.message?.includes('CSRF') ? 'Güvenlik hatası. Sayfayı yenileyin.' : 'Dosya yüklenirken bir hata oluştu.';
                } finally {
                    submitBtn.disabled = false;
                }
            });
        }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initExcelModal);
        else initExcelModal();
    })();
    </script>
    @endpush

    @push('styles')
    <style>
        .cursor-pointer { cursor: pointer; }
        details summary { list-style: none; }
        details summary::-webkit-details-marker { display: none; }
    </style>
    @endpush
@endif
