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
<div class="offcanvas offcanvas-start border-0 shadow-lg rounded-end-4" tabindex="-1" id="hasadCopilotOffcanvas" aria-labelledby="hasadCopilotLabel" style="width: 460px; max-width: 95vw; background: #f8fafc;">
    <div class="offcanvas-header bg-hasad text-white p-3 border-bottom border-success border-opacity-25" style="background: linear-gradient(135deg, #064E3B 0%, #047857 100%);">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-white text-success p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px;">
                <i class="fa-solid fa-robot fs-5"></i>
            </div>
            <div>
                <h6 class="offcanvas-title fw-bold mb-0 text-white" id="hasadCopilotLabel">مساعد حصاد الذكي (AI Copilot)</h6>
                <small class="text-white-50" style="font-size: 0.72rem;">متصل بقاعدة البيانات الوطنية اللحظية ومقيد بالصلاحيات الجغرافية</small>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas" aria-label="إغلاق"></button>
    </div>

    <div class="offcanvas-body p-3 d-flex flex-column" style="height: calc(100% - 130px); overflow-y: auto;">
        
        <!-- نصائح سريعة وأسئلة شائعة -->
        <div class="mb-3">
            <span class="text-muted small fw-bold d-block mb-2">
                <i class="fa-solid fa-bolt text-warning me-1"></i> استفسارات وتحليلات سريعة بضغطة زر:
            </span>
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
                            يمكنك سؤالي عن أي إحصائية، محافظة، جمعية تعاونية، محفظة قروض، أو توزيع للفرسان التنمويين، وسأقوم بتحليل البيانات وعرض المؤشرات والتوصيات فوراً وفق نطاقك الجغرافي المصرح.
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

<style>
    .copilot-typing-dots .dot {
        animation: copilotBlink 1.4s infinite both;
        font-size: 1.2rem;
        line-height: 0.8;
        font-weight: bold;
        color: #059669;
    }
    .copilot-typing-dots .dot:nth-child(2) { animation-delay: .2s; }
    .copilot-typing-dots .dot:nth-child(3) { animation-delay: .4s; }
    @keyframes copilotBlink {
        0%, 80%, 100% { opacity: 0; }
        40% { opacity: 1; }
    }
    .copilot-loading-card {
        border-right: 3px solid #059669 !important;
        animation: copilotPulse 1.8s infinite ease-in-out;
    }
    @keyframes copilotPulse {
        0%, 100% { box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        50% { box-shadow: 0 4px 12px rgba(5,150,105,0.15); }
    }
</style>

<script>
    function removeCopilotLoaders() {
        document.querySelectorAll('.copilot-loading').forEach(el => el.remove());
    }

    async function submitCopilotQuery(customPrompt = null) {
        const input = document.getElementById('copilotInput');
        const prompt = (customPrompt || (input ? input.value : '') || '').trim();
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
        if (!customPrompt && input) input.value = '';

        // تنظيف أي مؤشرات سابقة
        removeCopilotLoaders();

        // إضافة مؤشر جاري التحليل المتحرك
        const loadingMsg = document.createElement('div');
        loadingMsg.className = 'd-flex justify-content-start mb-2 copilot-loading';
        loadingMsg.innerHTML = `
            <div class="bg-white border text-dark p-2 px-3 rounded-4 small shadow-xs d-flex align-items-center gap-2 copilot-loading-card">
                <span class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 10px; height: 10px;"></span>
                <span class="text-secondary fw-medium">جاري تحليل البيانات في نظام حصاد</span>
                <span class="copilot-typing-dots d-inline-flex gap-1">
                    <span class="dot">.</span><span class="dot">.</span><span class="dot">.</span>
                </span>
            </div>
        `;
        container.appendChild(loadingMsg);
        container.scrollTop = container.scrollHeight;

        if (submitBtn) submitBtn.disabled = true;
        if (input) input.disabled = true;

        try {
            // قراءة أحدث رمز CSRF متاح
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '{{ csrf_token() }}';
            
            // استخدام مسار نسبي مع مسار بديل تلقائي لضمان التوافق المطلق مع بيئة Vercel Serverless
            const primaryEndpoint = '{{ route("api.copilot.query", [], false) }}' || '/api/copilot/query';
            const fallbackEndpoint = '{{ route("copilot.query", [], false) }}' || '/copilot/query';

            let response = null;
            let usedFallback = false;

            try {
                response = await fetch(primaryEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ prompt: prompt })
                });

                if (response.status === 404 && fallbackEndpoint && fallbackEndpoint !== primaryEndpoint) {
                    usedFallback = true;
                    response = await fetch(fallbackEndpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ prompt: prompt })
                    });
                }
            } catch (netErr) {
                if (!usedFallback && fallbackEndpoint && fallbackEndpoint !== primaryEndpoint) {
                    response = await fetch(fallbackEndpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ prompt: prompt })
                    });
                } else {
                    throw netErr;
                }
            }

            removeCopilotLoaders();

            const contentType = response.headers.get('content-type') || '';
            let data = null;

            if (contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const textResp = await response.text();
                throw new Error(response.statusText || 'استجابة غير صالحة من الخادم');
            }

            if (response.ok && data && data.status === 'success' && data.data) {
                renderCopilotResponse(data.data, container);
            } else {
                let errorMsg = (data && (data.message || data.error));
                if (response.status === 401) {
                    errorMsg = 'انتهت جلستك، يرجى تسجيل الدخول مجدداً للمتابعة.';
                } else if (response.status === 403) {
                    errorMsg = 'عذراً، تم حظر الاستعلام لعدم توفر الصلاحية المطلوبة.';
                } else if (response.status === 404) {
                    errorMsg = 'عذراً، تعذر الوصول لنقطة خدمة المساعد الذكي حالياً.';
                } else if (!errorMsg) {
                    errorMsg = 'حدث خطأ أثناء معالجة الاستفسار.';
                }
                renderCopilotError(errorMsg, container, prompt);
            }
        } catch (err) {
            removeCopilotLoaders();
            console.error('Copilot Error:', err);
            renderCopilotError('تعذر الاتصال بخادم التحليل الذكي. يرجى التأكد من اتصال الإنترنت والمحاولة مرة أخرى.', container, prompt);
        } finally {
            if (submitBtn) submitBtn.disabled = false;
            if (input) {
                input.disabled = false;
                input.focus();
            }
            container.scrollTop = container.scrollHeight;
        }
    }

    function renderCopilotResponse(d, container) {
        const respBox = document.createElement('div');
        respBox.className = 'd-flex justify-content-start mb-3';
        
        const isRestricted = d.type === 'restricted';
        const cardBorder = isRestricted ? 'border-warning' : 'border-success border-opacity-25';
        const titleColor = isRestricted ? 'text-warning' : 'text-success';
        const headerIcon = isRestricted ? 'fa-lock' : 'fa-brain';

        let kpisHtml = '';
        if (d.kpis && d.kpis.length) {
            kpisHtml = `
                <div class="row g-1 my-2 text-center">
                    ${d.kpis.map(k => `
                        <div class="col-6">
                            <div class="p-2 bg-light rounded-2 border">
                                <span class="text-muted d-block" style="font-size: 0.68rem;">${escapeHtml(k.label)}</span>
                                <strong class="text-dark" style="font-size: 0.85rem;">${escapeHtml(k.value)}</strong>
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
                    <span class="text-muted small fw-bold d-block mb-1" style="font-size: 0.7rem;">
                        <i class="fa-solid fa-lightbulb text-warning me-1"></i> التوصيات والملاحظات:
                    </span>
                    <ul class="mb-0 ps-3 small text-muted" style="font-size: 0.75rem; line-height: 1.5;">
                        ${d.recommendations.map(r => `<li>${escapeHtml(r)}</li>`).join('')}
                    </ul>
                </div>
            `;
        }

        let actionHtml = '';
        if (d.action_link && d.action_label) {
            const btnClass = isRestricted ? 'btn-outline-warning text-dark' : 'btn-outline-success';
            actionHtml = `
                <div class="mt-2 text-start">
                    <a href="${escapeHtml(d.action_link)}" class="btn btn-sm ${btnClass} py-1 px-2" style="font-size: 0.75rem;">
                        ${escapeHtml(d.action_label)} <i class="fa-solid fa-arrow-left ms-1"></i>
                    </a>
                </div>
            `;
        }

        respBox.innerHTML = `
            <div class="bg-white border ${cardBorder} rounded-4 p-3 shadow-xs" style="max-width: 95%;">
                <div class="d-flex align-items-center gap-1 mb-1 ${titleColor} fw-bold small">
                    <i class="fa-solid ${headerIcon}"></i>
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

    function renderCopilotError(msg, container, failedPrompt = null) {
        const errBox = document.createElement('div');
        errBox.className = 'd-flex justify-content-start mb-2';
        errBox.innerHTML = `
            <div class="alert alert-danger p-2 px-3 rounded-3 small mb-0 w-100 shadow-xs">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <i class="fa-solid fa-circle-exclamation me-1"></i> ${escapeHtml(msg)}
                    </div>
                    ${failedPrompt ? `<button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill retry-copilot-btn" style="font-size: 0.72rem;">إعادة المحاولة</button>` : ''}
                </div>
            </div>
        `;

        if (failedPrompt) {
            errBox.querySelector('.retry-copilot-btn')?.addEventListener('click', function() {
                errBox.remove();
                submitCopilotQuery(failedPrompt);
            });
        }

        container.appendChild(errBox);
    }

    function escapeHtml(text) {
        if (!text && text !== 0) return '';
        const div = document.createElement('div');
        div.textContent = String(text);
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
