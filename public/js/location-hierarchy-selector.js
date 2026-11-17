/**
 * Dynamic 4-Level Location Hierarchy Cascading & Quick Add Driver
 * نظام حصاد - التقسيم الإداري والمستوى الرابع الديناميكي
 */

document.addEventListener('DOMContentLoaded', function () {
    initAllLocationSelectors();
});

function initAllLocationSelectors() {
    const containers = document.querySelectorAll('.location-hierarchy-selector');
    containers.forEach(container => {
        if (!container.dataset.initialized) {
            setupLocationHierarchySelector(container);
            container.dataset.initialized = 'true';
        }
    });
}

function setupLocationHierarchySelector(container) {
    const govSelect = container.querySelector('.selector-gov');
    const dirSelect = container.querySelector('.selector-dir');
    const uzlSelect = container.querySelector('.selector-uzl');
    const subSelect = container.querySelector('.selector-sub');
    const addButtons = container.querySelectorAll('.btn-open-quick-add');
    const modalEl = container.querySelector('.modal-quick-add-sub');
    const formEl = container.querySelector('.form-quick-add-sub');
    const errorFeedback = container.querySelector('.error-feedback');
    const entityTypeFilter = container.dataset.entityType || '';

    const initialGov = container.dataset.selectedGov || '';
    const initialDir = container.dataset.selectedDir || '';
    const initialUzl = container.dataset.selectedUzl || '';
    const initialSub = container.dataset.selectedSub || '';

    let bsModal = null;
    if (modalEl && typeof bootstrap !== 'undefined') {
        bsModal = new bootstrap.Modal(modalEl);
    }

    // فتح النافذة المصغرة
    addButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            if (!uzlSelect.value) {
                alert('يرجى اختيار العزلة أولاً لإضافة الموقع تحتها');
                return;
            }
            if (errorFeedback) errorFeedback.classList.add('d-none');
            if (formEl) formEl.reset();
            if (bsModal) bsModal.show();
        });
    });

    // تبديل إظهار زر الإضافة حسب اختيار العزلة
    function toggleAddButtons() {
        const uzlVal = uzlSelect.value;
        addButtons.forEach(btn => {
            btn.style.display = uzlVal ? 'inline-flex' : 'none';
        });
    }

    // 1. عند تغيير المحافظة
    govSelect.addEventListener('change', function () {
        const govId = this.value;
        resetSelect(dirSelect, '-- اختر المديرية --');
        resetSelect(uzlSelect, '-- اختر العزلة --');
        resetSelect(subSelect, '-- اختر القرية / الجمعية --');
        toggleAddButtons();

        if (!govId) return;

        dirSelect.disabled = true;
        dirSelect.options[0].text = 'جاري التحميل...';

        fetch(`/geo/districts/${govId}`)
            .then(res => res.json())
            .then(data => {
                resetSelect(dirSelect, '-- اختر المديرية --');
                dirSelect.disabled = false;
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.name_ar || item.name;
                    dirSelect.appendChild(opt);
                });
            })
            .catch(err => {
                console.error(err);
                resetSelect(dirSelect, '-- خطأ في التحميل --');
            });
    });

    // 2. عند تغيير المديرية
    dirSelect.addEventListener('change', function () {
        const dirId = this.value;
        resetSelect(uzlSelect, '-- اختر العزلة --');
        resetSelect(subSelect, '-- اختر القرية / الجمعية --');
        toggleAddButtons();

        if (!dirId) return;

        uzlSelect.disabled = true;
        uzlSelect.options[0].text = 'جاري التحميل...';

        fetch(`/geo/sub-districts/${dirId}`)
            .then(res => res.json())
            .then(data => {
                resetSelect(uzlSelect, '-- اختر العزلة --');
                uzlSelect.disabled = false;
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.name_ar || item.name;
                    uzlSelect.appendChild(opt);
                });
            })
            .catch(err => {
                console.error(err);
                resetSelect(uzlSelect, '-- خطأ في التحميل --');
            });
    });

    // 3. عند تغيير العزلة
    uzlSelect.addEventListener('change', function () {
        const uzlId = this.value;
        resetSelect(subSelect, '-- اختر القرية / الجمعية --');
        toggleAddButtons();

        if (!uzlId) return;

        loadSubLocations(uzlId);
    });

    function loadSubLocations(uzlId, autoSelectId = null) {
        subSelect.disabled = true;
        subSelect.options[0].text = 'جاري التحميل...';

        const url = `/api/locations/sub-locations/by-uzlah/${uzlId}` +
            (entityTypeFilter ? `?type=${encodeURIComponent(entityTypeFilter)}` : '');

        fetch(url)
            .then(res => res.json())
            .then(data => {
                resetSelect(subSelect, '-- اختر القرية / الجمعية --');
                subSelect.disabled = false;
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.name_ar + (item.entity_type ? ` (${item.entity_type})` : '');
                    subSelect.appendChild(opt);
                });

                if (autoSelectId) {
                    subSelect.value = autoSelectId;
                    subSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
            })
            .catch(err => {
                console.error(err);
                resetSelect(subSelect, '-- خطأ في التحميل --');
            });
    }

    // 4. إرسال نموذج الإضافة السريعة (Inline Modal Submission)
    if (formEl) {
        formEl.addEventListener('submit', function (e) {
            e.preventDefault();
            const uzlahId = uzlSelect.value;
            const nameInput = formEl.querySelector('.input-sub-name');
            const typeSelect = formEl.querySelector('.select-sub-type');
            const notesInput = formEl.querySelector('.input-sub-notes');
            const submitBtn = formEl.querySelector('.btn-submit-sub');

            if (!nameInput.value.trim()) {
                showModalError('يرجى كتابة الاسم');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري الحفظ...';

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch('/api/locations/sub-locations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                },
                body: JSON.stringify({
                    uzlah_id: uzlahId,
                    name_ar: nameInput.value.trim(),
                    entity_type: typeSelect ? typeSelect.value : 'قرية',
                    notes: notesInput ? notesInput.value.trim() : '',
                }),
            })
            .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, data })))
            .then(result => {
                if (!result.ok && result.status !== 200) {
                    throw new Error(result.data.message || 'حدث خطأ أثناء الحفظ');
                }

                const item = result.data.data;

                // إعادة تحميل القائمة واختيار العنصر تلقائياً
                loadSubLocations(uzlahId, item.id);

                if (bsModal) bsModal.hide();
                formEl.reset();
            })
            .catch(err => {
                showModalError(err.message);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> حفظ وتحديد تلقائي';
            });
        });
    }

    function showModalError(msg) {
        if (errorFeedback) {
            errorFeedback.textContent = msg;
            errorFeedback.classList.remove('d-none');
        } else {
            alert(msg);
        }
    }

    function resetSelect(selectEl, defaultText) {
        selectEl.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = defaultText;
        selectEl.appendChild(opt);
        selectEl.disabled = true;
    }

    // التحميل المسبق للقيم الابتدائية إن وُجدت
    if (initialGov) {
        fetch(`/geo/districts/${initialGov}`)
            .then(res => res.json())
            .then(dirs => {
                resetSelect(dirSelect, '-- اختر المديرية --');
                dirSelect.disabled = false;
                dirs.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.name_ar || item.name;
                    if (String(item.id) === String(initialDir)) opt.selected = true;
                    dirSelect.appendChild(opt);
                });

                if (initialDir) {
                    return fetch(`/geo/sub-districts/${initialDir}`);
                }
            })
            .then(res => res ? res.json() : null)
            .then(uzls => {
                if (!uzls) return;
                resetSelect(uzlSelect, '-- اختر العزلة --');
                uzlSelect.disabled = false;
                uzls.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.name_ar || item.name;
                    if (String(item.id) === String(initialUzl)) opt.selected = true;
                    uzlSelect.appendChild(opt);
                });
                toggleAddButtons();

                if (initialUzl) {
                    loadSubLocations(initialUzl, initialSub);
                }
            })
            .catch(err => console.error("Initial load error:", err));
    }
}
