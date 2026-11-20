@extends('layouts.app')

@section('title', 'الاستيراد الذكي الشامل من ملفات الإكسل - نظام حصاد')

@section('content')
<div class="container-fluid px-0">
    
    <!-- ترويسة الصفحة والتنقل -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">لوحة التحكم</a></li>
                    <li class="breadcrumb-item active" aria-current="page">أدوات النظام</li>
                    <li class="breadcrumb-item active" aria-current="page">الاستيراد الذكي الشامل</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-excel text-success" data-lucide="file-spreadsheet"></i>
                <span>الاستيراد الذكي الشامل من ملفات الإكسل</span>
                <span class="badge bg-success-subtle text-success border px-2 py-1 fs-6">Smart Import 2.0</span>
            </h3>
            <p class="text-muted small mb-0 mt-1">محرك متقدم لمعالجة وتسكين ملفات Excel و CSV متعددة الأوراق بدقة 100% مع ضمان عدم فقدان البيانات والربط الجغرافي الآلي</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('geography.download_template') }}" class="btn btn-hasad-outline d-flex align-items-center gap-1">
                <i class="fa-solid fa-download"></i>
                <span>تحميل قالب معتمد</span>
            </a>
            <a href="{{ route('geography.manage') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                <i class="fa-solid fa-network-wired"></i>
                <span>إدارة النطاق الجغرافي</span>
            </a>
        </div>
    </div>

    <!-- تنبيهات النجاح والخطأ -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center p-3 mb-4 rounded-3 shadow-sm border border-success" role="alert">
            <i class="fa-solid fa-circle-check fs-4 me-3 text-success"></i>
            <div>
                <strong class="d-block mb-1">تمت العملية بنجاح!</strong>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center p-3 mb-4 rounded-3 shadow-sm border border-danger" role="alert">
            <i class="fa-solid fa-triangle-exclamation fs-4 me-3 text-danger"></i>
            <div>
                <strong class="d-block mb-1">تنبيه أثناء المعالجة:</strong>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- بطاقات إحصائيات البيانات المسكنة حالياً -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card-hasad p-3 text-center h-100">
                <span class="text-muted small d-block mb-1">الجمعيات التعاونية</span>
                <h4 class="stat-num text-dark fw-bold mb-0">{{ number_format($stats['associations']) }}</h4>
                <span class="badge bg-success-subtle text-success border px-2 py-0 mt-2 font-monospace" style="font-size: 0.65rem;">نشطة</span>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card-hasad p-3 text-center h-100">
                <span class="text-muted small d-block mb-1">المبادرات المجتمعية</span>
                <h4 class="stat-num text-dark fw-bold mb-0">{{ number_format($stats['initiatives']) }}</h4>
                <span class="badge bg-primary-subtle text-primary border px-2 py-0 mt-2 font-monospace" style="font-size: 0.65rem;">مبادرة منفذة</span>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card-hasad p-3 text-center h-100">
                <span class="text-muted small d-block mb-1">الفرسان والكوادر الموحد</span>
                <h4 class="stat-num text-dark fw-bold mb-0">{{ number_format($stats['personnel']) }}</h4>
                <span class="badge bg-info-subtle text-info border px-2 py-0 mt-2 font-monospace" style="font-size: 0.65rem;">31 تخصصاً</span>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card-hasad p-3 text-center h-100">
                <span class="text-muted small d-block mb-1">الهيئات والقيادات</span>
                <h4 class="stat-num text-dark fw-bold mb-0">{{ number_format($stats['board_members']) }}</h4>
                <span class="badge bg-secondary-subtle text-secondary border px-2 py-0 mt-2 font-monospace" style="font-size: 0.65rem;">قيادي ومحاسب</span>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card-hasad p-3 text-center h-100">
                <span class="text-muted small d-block mb-1">برامج القروض والتمكين</span>
                <h4 class="stat-num text-dark fw-bold mb-0">{{ number_format($stats['loans']) }}</h4>
                <span class="badge bg-warning-subtle text-warning border px-2 py-0 mt-2 font-monospace" style="font-size: 0.65rem;">قروض بيضاء</span>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card-hasad p-3 text-center h-100">
                <span class="text-muted small d-block mb-1">القطاعات الإنتاجية</span>
                <h4 class="stat-num text-dark fw-bold mb-0">{{ number_format($stats['land_reclamations'] + $stats['contract_farming'] + $stats['crop_expansions'] + $stats['nurseries'] + $stats['dairy_livestock']) }}</h4>
                <span class="badge bg-emerald-500/10 text-success border px-2 py-0 mt-2 font-monospace" style="font-size: 0.65rem;">مشروع وسجل</span>
            </div>
        </div>
    </div>

    <!-- شبكة أدوات الاستيراد والتسكين -->
    <div class="row g-4 mb-4">
        
        <!-- القسم الأول: رفع واستيراد ملف إكسل ذكي -->
        <div class="col-lg-7">
            <div class="card-hasad h-100 p-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 bg-success-subtle text-success rounded-3">
                            <i class="fa-solid fa-cloud-arrow-up fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">رفع واستيراد ملف Excel أو CSV</h5>
                            <span class="text-muted small">التعرف التلقائي على الأعمدة، مطابقة المحافظات، وإنشاء العزل والقرى آلياً</span>
                        </div>
                    </div>
                </div>

                <form action="{{ route('import.smart.upload') }}" method="POST" enctype="multipart/form-data" id="smartUploadForm">
                    @csrf
                    
                    <!-- منطقة السحب والإفلات التفاعلية -->
                    <div class="p-4 border-2 border-dashed rounded-4 text-center mb-3 bg-light position-relative" style="border-color: #A7F3D0 !important; cursor: pointer;" onclick="document.getElementById('excel_file').click()">
                        <i class="fa-solid fa-file-excel fs-1 text-success mb-2 d-block"></i>
                        <h6 class="fw-bold text-dark mb-1">اضغط هنا لاختيار الملف أو اسحبه وأفلته هنا</h6>
                        <p class="text-muted small mb-2">يدعم ملفات XLSX, XLS, CSV, TSV (حتى 50 ميجابايت)</p>
                        
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 bg-white rounded-pill border small text-muted font-monospace" id="fileInfoBox" style="display: none !important;">
                            <i class="fa-solid fa-paperclip text-success"></i>
                            <span id="fileName">لم يتم اختيار ملف</span>
                            <span class="badge bg-light text-dark border ms-1" id="fileSize">0 KB</span>
                        </div>

                        <input type="file" name="excel_file" id="excel_file" class="d-none" accept=".xlsx,.xls,.csv,.txt,.tsv" required onchange="handleFileSelect(this)">
                    </div>

                    <!-- إرشادات المحرك الذكي -->
                    <div class="bg-emerald-500/5 border border-emerald-500/20 rounded-3 p-3 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fa-solid fa-wand-magic-sparkles text-success"></i>
                            <strong class="text-dark small">مميزات محرك الاستيراد الذكي (Smart Engine):</strong>
                        </div>
                        <ul class="mb-0 small text-muted ps-3" style="line-height: 1.7;">
                            <li><strong>مرونة الأعمدة:</strong> لا يشترط ترتيب محدد للأعمدة (يكتشف أسماء الحقول تلقائياً بالذكاء النمطي).</li>
                            <li><strong>معالجة التشكيل والمسميات:</strong> توحيد الهمزات والتاء المربوطة والألف المقصورة وإزالة الكلمات الزائدة.</li>
                            <li><strong>إنشاء العزل تلقائياً:</strong> في حال وجود عزلة جديدة في الملف يقوم النظام بإنشائها فوراً دون رفض الملف.</li>
                            <li><strong>حفظ متعدد متزامن:</strong> تسكين البيانات فوراً في جدول المستوى الرابع، وجدول القرى، وسجل الجمعيات المركزي.</li>
                        </ul>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" class="btn btn-hasad-primary px-4 py-2" id="btnSubmitImport">
                            <i class="fa-solid fa-upload me-1"></i>
                            <span>بدء الاستيراد والمعالجة الذكية</span>
                        </button>
                        <span class="text-muted small font-monospace">ترميز UTF-8 & Windows-1256 مدعوم تلقائياً</span>
                    </div>
                </form>
            </div>
        </div>

        <!-- القسم الثاني: التسكين السريع لملف بيانات.xlsx المركزي -->
        <div class="col-lg-5">
            <div class="card-hasad h-100 p-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 bg-emerald-500/10 text-success rounded-3">
                            <i class="fa-solid fa-database fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">التسكين الآلي للملف المركزي</h5>
                            <span class="text-muted small">قاعدة البيانات الكبرى (بيانات.xlsx)</span>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">الملف المستهدف:</span>
                        <span class="badge bg-white text-dark border font-monospace">بيانات.xlsx</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">الموقع:</span>
                        <span class="text-dark small fw-semibold font-monospace">سطح المكتب (Desktop)</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">الحجم التقريبي:</span>
                        <span class="text-success small fw-bold font-monospace">{{ $masterFileSize > 0 ? $masterFileSize . ' MB' : 'غير متوفر' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">حالة الملف:</span>
                        @if($masterFileExists)
                            <span class="badge bg-success text-white">متوفر وجاهز للتسكين</span>
                        @else
                            <span class="badge bg-warning text-dark">الملف غير موجود بالمسار</span>
                        @endif
                    </div>
                </div>

                <p class="text-muted small mb-4" style="line-height: 1.6;">
                    يقوم هذا الزر بتشغيل محرك المعالجة الشامل الذي يقرأ **أوراق العمل الـ 60 كاملة**، ويقوم بتسكين الجمعيات، المبادرات، مجالس الإدارة، القروض، المحاصيل، المشاتل، وسجل الفرسان والكوادر الموحد مباشرة في قاعدة البيانات.
                </p>

                <form action="{{ route('import.smart.master') }}" method="POST" onsubmit="return confirm('هل ترغب في بدء تسكين وتحديث كافة السجلات من ملف بيانات.xlsx؟ قد تستغرق العملية دقيقة واحدة.')">
                    @csrf
                    <button type="submit" class="btn btn-hasad-outline w-100 py-3 d-flex align-items-center justify-content-center gap-2" {{ !$masterFileExists ? 'disabled' : '' }}>
                        <i class="fa-solid fa-bolt-lightning text-warning fs-5"></i>
                        <span class="fw-bold">تشغيل التسكين الشامل من (بيانات.xlsx)</span>
                    </button>
                </form>

                <div class="border-top mt-4 pt-3 text-center">
                    <span class="text-muted small">
                        <i class="fa-solid fa-shield-halved text-success me-1"></i>
                        معالجة مؤمنة بمعاملات قاعدة البيانات (DB Transactions)
                    </span>
                </div>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const name = file.name;
            const sizeKB = (file.size / 1024).toFixed(1);
            const sizeMB = (file.size / 1024 / 1024).toFixed(2);
            
            document.getElementById('fileName').textContent = name;
            document.getElementById('fileSize').textContent = sizeMB >= 1 ? `${sizeMB} MB` : `${sizeKB} KB`;
            document.getElementById('fileInfoBox').style.setProperty('display', 'inline-flex', 'important');
        }
    }
</script>
@endpush
@endsection
