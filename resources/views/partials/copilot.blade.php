<!-- زر عائم لمساعد حصاد الذكي (Floating Copilot Trigger) -->
<div class="position-fixed bottom-0 start-0 m-3 m-md-4" style="z-index: 1040;">
    <button type="button" class="btn btn-hasad-primary rounded-pill px-3 py-2 shadow-lg d-flex align-items-center gap-2 border border-2 border-white" data-bs-toggle="offcanvas" data-bs-target="#hasadCopilotOffcanvas" aria-controls="hasadCopilotOffcanvas" id="btnOpenCopilot">
        <span class="spinner-grow spinner-grow-sm text-warning" role="status" style="width: 8px; height: 8px;"></span>
        <i class="fa-solid fa-wand-magic-sparkles"></i>
        <span class="fw-bold small d-none d-sm-inline">مساعد حصاد الذكي</span>
        <span class="badge bg-warning text-dark rounded-pill px-2 py-0" style="font-size: 0.65rem;">AI</span>
    </button>
</div>

<!-- لوحة المساعد الذكي الجانبية (Copilot Slide-Over Drawer) -->
<div class="offcanvas offcanvas-start border-0 shadow-lg rounded-end-4" tabindex="-1" id="hasadCopilotOffcanvas" aria-labelledby="hasadCopilotLabel" style="width: 440px; max-width: 95vw; background: #f8fafc;">
    <div class="offcanvas-header bg-hasad text-white p-3 border-bottom border-success border-opacity-25" style="background: linear-gradient(135deg, #064E3B 0%, #047857 100%);">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-white text-success p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px;">
                <i class="fa-solid fa-robot fs-5"></i>
            </div>
            <div>
                <h6 class="offcanvas-title fw-bold mb-0 text-white" id="hasadCopilotLabel">مساعد حصاد الذكي (AI Copilot)</h6>
                <small class="text-white-50" style="font-size: 0.72rem;">متصل بقاعدة البيانات الوطنية اللحظية</small>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas" aria-label="إغلاق"></button>
    </div>

    <div class="offcanvas-body p-3 d-flex flex-column" style="height: calc(100% - 130px); overflow-y: auto;">
        
        <!-- نصائح سريعة وأسئلة شائعة -->
        <div class="mb-3">
            <span class="text-muted small fw-bold d-block mb-2">استفسارات وتحليلات سريعة بضغطة زر:</span>
            <div class="d-flex flex-wrap gap-1">
                <button type="button" class="btn btn-sm btn-white border rounded-pill px-2 py-1 small text-dark copilot-chip" data-prompt="الملخص الوطني العام للنظام">
                    📊 الملخص العام
                </button>
                <button type="button" class="btn btn-sm btn-white border rounded-pill px-2 py-1 small text-dark copilot-chip" data-prompt="تحليل وضع الجمعيات التعاونية ورأس المال">
                    🏛️ الجمعيات التعاونية
                </button>
                <button type="button" class="btn btn-sm btn-white border rounded-pill px-2 py-1 small text-dark copilot-chip" data-prompt="إحصائيات الفرسان والكوادر والتفعيل الميداني">
                    👥 الفرسان والكوادر
                </button>
                <button type="button" class="btn btn-sm btn-white border rounded-pill px-2 py-1 small text-dark copilot-chip" data-prompt="تقرير القروض البيضاء ونسبة السداد">
                    💰 القروض البيضاء
                </button>
                <button type="button" class="btn btn-sm btn-white border rounded-pill px-2 py-1 small text-dark copilot-chip" data-prompt="تحليل محافظة ذمار">
                    📍 محافظة ذمار
                </button>
                <button type="button" class="btn btn-sm btn-white border rounded-pill px-2 py-1 small text-dark copilot-chip" data-prompt="تحليل محافظة صنعاء">
                    📍 محافظة صنعاء
                </button>
                <button type="button" class="btn btn-sm btn-white border rounded-pill px-2 py-1 small text-dark copilot-chip" data-prompt="تحليل محافظة الحديدة">
                    📍 محافظة الحديدة
                </button>
            </div>
        </div>

        <hr class="my-2 opacity-25">

        <!-- صندوق المحادثة وعرض النتائج -->
        <div id="copilotMessagesContainer" class="flex-grow-1 overflow-y-auto mb-2 pe-1">
            <div class="alert bg-white border rounded-3 p-3 shadow-xs mb-3">
                <div class="d-flex gap-2">
                    <span class="text-success fs-5"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
                    <div>
                        <strong class="d-block text-dark small mb-1">أهلاً بك في مساعد حصاد الذكي!</strong>
                        <p class="text-muted small mb-0" style="line-height: 1.6;">
                            يمكنك سؤالي عن أي إحصائية، محافظة، جمعية تعاونية، محفظة قروض، أو توزيع للفرسان التنمويين، وسأقوم بتحليل البيانات وعرض المؤشرات والتوصيات فوراً.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- شريط إدخال الأسئلة -->
    <div class="p-3 bg-white border-top shadow-sm">
        <form id="copilotForm" onsubmit="event.preventDefault(); submitCopilotQuery();">
            <div class="input-group">
                <input type="text" id="copilotInput" class="form-control rounded-start-pill ps-3" placeholder="اسأل عن أي محافظة، جمعية، أو قرض..." autocomplete="off">
                <button type="submit" class="btn btn-hasad-primary rounded-end-pill px-3" id="copilotSubmitBtn">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function submitCopilotQuery(customPrompt = null) {
        const input = document.getElementById('copilotInput');
        const prompt = customPrompt || input.value.trim();
        if (!prompt) return;

        const container = document.getElementById('copilotMessagesContainer');
        const submitBtn = document.getElementById('copilotSubmitBtn');

        // إضافة رسالة المستخدم
        const userMsg = document.createElement('div');
        userMsg.className = 'd-flex justify-content-end mb-2';
        userMsg.innerHTML = `
            <div class="bg-success text-white p-2 px-3 rounded-4 small shadow-xs" style="max-width: 85%;">
                <i class="fa-solid fa-user me-1 opacity-75"></i> ${escapeHtml(prompt)}
            </div>
        `;
        container.appendChild(userMsg);
        if (!customPrompt) input.value = '';

        // إضافة مؤشر جاري التحليل
        const loadingMsg = document.createElement('div');
        loadingMsg.className = 'd-flex justify-content-start mb-2 copilot-loading';
        loadingMsg.innerHTML = `
            <div class="bg-white border text-dark p-2 px-3 rounded-4 small shadow-xs d-flex align-items-center gap-2">
                <span class="spinner-border spinner-border-sm text-success" role="status"></span>
                <span>جاري تحليل البيانات في نظام حصاد...</span>
            </div>
        `;
        container.appendChild(loadingMsg);
        container.scrollTop = container.scrollHeight;

        submitBtn.disabled = true;

        fetch('{{ route("api.copilot.query") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ prompt: prompt })
        })
        .then(res => res.json())
        .then(data => {
            loadingMsg.remove();
            submitBtn.disabled = false;

            if (data.status === 'success' && data.data) {
                renderCopilotResponse(data.data, container);
            } else {
                renderCopilotError(data.message || 'حدث خطأ أثناء معالجة الاستفسار.', container);
            }
            container.scrollTop = container.scrollHeight;
        })
        .catch(err => {
            loadingMsg.remove();
            submitBtn.disabled = false;
            renderCopilotError('تعذر الاتصال بخادم التحليل الذكي.', container);
            container.scrollTop = container.scrollHeight;
        });
    }

    function renderCopilotResponse(d, container) {
        const respBox = document.createElement('div');
        respBox.className = 'd-flex justify-content-start mb-3';
        
        let kpisHtml = '';
        if (d.kpis && d.kpis.length) {
            kpisHtml = `
                <div class="row g-1 my-2 text-center">
                    ${d.kpis.map(k => `
                        <div class="col-6">
                            <div class="p-2 bg-light rounded-2 border">
                                <span class="text-muted d-block" style="font-size: 0.68rem;">${k.label}</span>
                                <strong class="text-dark" style="font-size: 0.85rem;">${k.value}</strong>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        let recsHtml = '';
        if (d.recommendations && d.recommendations.length) {
            recsHtml = `
                <div class="mt-2 pt-2 border-top">
                    <span class="text-muted small fw-bold d-block mb-1" style="font-size: 0.7rem;"><i class="fa-solid fa-lightbulb text-warning me-1"></i> التوصيات المقترحة:</span>
                    <ul class="mb-0 ps-3 small text-muted" style="font-size: 0.75rem; line-height: 1.5;">
                        ${d.recommendations.map(r => `<li>${r}</li>`).join('')}
                    </ul>
                </div>
            `;
        }

        let actionHtml = '';
        if (d.action_link && d.action_label) {
            actionHtml = `
                <div class="mt-2 text-start">
                    <a href="${d.action_link}" class="btn btn-sm btn-outline-success py-1 px-2" style="font-size: 0.75rem;">
                        ${d.action_label} <i class="fa-solid fa-arrow-left ms-1"></i>
                    </a>
                </div>
            `;
        }

        respBox.innerHTML = `
            <div class="bg-white border rounded-4 p-3 shadow-xs" style="max-width: 95%;">
                <div class="d-flex align-items-center gap-1 mb-1 text-success fw-bold small">
                    <i class="fa-solid fa-brain"></i>
                    <span>${escapeHtml(d.title)}</span>
                </div>
                <p class="text-dark small mb-1" style="line-height: 1.6; font-size: 0.8rem;">
                    ${escapeHtml(d.summary)}
                </p>
                ${kpisHtml}
                ${recsHtml}
                ${actionHtml}
            </div>
        `;
        container.appendChild(respBox);
    }

    function renderCopilotError(msg, container) {
        const errBox = document.createElement('div');
        errBox.className = 'd-flex justify-content-start mb-2';
        errBox.innerHTML = `
            <div class="alert alert-danger p-2 px-3 rounded-3 small mb-0">
                <i class="fa-solid fa-circle-exclamation me-1"></i> ${escapeHtml(msg)}
            </div>
        `;
        container.appendChild(errBox);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ربط الأزرار السريعة
    document.querySelectorAll('.copilot-chip').forEach(btn => {
        btn.addEventListener('click', function() {
            const prompt = this.getAttribute('data-prompt');
            submitCopilotQuery(prompt);
        });
    });
</script>
