@extends('layouts.app')

@section('title', "ملف جمعية: {$association->name} - نظام حصاد")

@section('content')
<div class="container-fluid p-0">

    <!-- شريط العنوان والملخص العلوي للجمعية -->
    <div class="card-hasad mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success-subtle text-success p-3 me-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 68px; height: 68px;">
                        <i class="fa-solid fa-building-columns fs-2"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h4 class="fw-bold mb-0 text-dark">{{ $association->name }}</h4>
                            <span class="badge bg-light text-dark border font-monospace">{{ $association->code }}</span>
                            @if($association->status === 'نشطة')
                                <span class="badge-status-active">نشطة</span>
                            @elseif($association->status === 'تحت التأسيس')
                                <span class="badge-status-pending">تحت التأسيس</span>
                            @elseif($association->status === 'متعثرة')
                                <span class="badge-status-danger">متعثرة</span>
                            @else
                                <span class="badge bg-secondary">{{ $association->status }}</span>
                            @endif
                        </div>
                        <div class="small text-muted mt-1">
                            <i class="fa-solid fa-location-dot text-danger me-1"></i>
                            {{ $association->governorate?->name_ar }} - مديرية {{ $association->directorate?->name_ar }} - عزلة {{ $association->uzlah?->name_ar }}
                            @if($association->subLocation) - قرية {{ $association->subLocation->name_ar }} @endif
                            | <span class="badge bg-light text-dark border">{{ $association->type }}</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('associations.edit', $association->id) }}" class="btn btn-hasad-primary d-flex align-items-center gap-1 shadow-sm">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>تعديل بيانات الجمعية</span>
                    </a>
                    <a href="{{ route('associations.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1 shadow-sm">
                        <i class="fa-solid fa-arrow-right"></i>
                        <span>القائمة الرئيسية</span>
                    </a>
                </div>
            </div>

            <hr class="my-3 opacity-25">

            <!-- شريط المؤشرات السريعة للجمعية -->
            <div class="row g-2 text-center small">
                <div class="col-6 col-md-2">
                    <span class="text-muted d-block">إجمالي الأعضاء</span>
                    <strong class="fs-6 text-dark stat-num">{{ number_format($association->total_members) }}</strong>
                </div>
                <div class="col-6 col-md-2">
                    <span class="text-muted d-block">رأس المال الحالي</span>
                    <strong class="fs-6 text-dark stat-num">{{ number_format($association->current_capital) }} ر.ي</strong>
                </div>
                <div class="col-6 col-md-2">
                    <span class="text-muted d-block">المشاريع المنفذة</span>
                    <strong class="fs-6 text-success stat-num">{{ $association->projects->count() ?: $association->implemented_projects_count }}</strong>
                </div>
                <div class="col-6 col-md-2">
                    <span class="text-muted d-block">المجاميع الإنتاجية</span>
                    <strong class="fs-6 text-primary stat-num">{{ $association->productiveGroups->count() }}</strong>
                </div>
                <div class="col-6 col-md-2">
                    <span class="text-muted d-block">الكوادر والفرسان</span>
                    <strong class="fs-6 text-info stat-num">{{ $association->personnel->count() }}</strong>
                </div>
                <div class="col-6 col-md-2">
                    <span class="text-muted d-block">القروض البيضاء</span>
                    <strong class="fs-6 text-warning stat-num">{{ $association->loans->count() }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- رسائل التنبيه والنجاح -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- بطاقة التبويبات الـ 9 الشاملة (The 9 Tabs of Association Dossier) -->
    <div class="card-hasad">
        <div class="card-header bg-white p-2 border-bottom">
            <ul class="nav nav-pills nav-fill flex-column flex-md-row gap-1" id="associationTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold py-2 small" id="tab-basic-tab" data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" role="tab">
                        <i class="fa-solid fa-circle-info me-1"></i> 1. الأساسية
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-2 small" id="tab-legal-tab" data-bs-toggle="tab" data-bs-target="#tab-legal" type="button" role="tab">
                        <i class="fa-solid fa-scale-balanced me-1"></i> 2. القانونية
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-2 small" id="tab-leadership-tab" data-bs-toggle="tab" data-bs-target="#tab-leadership" type="button" role="tab">
                        <i class="fa-solid fa-users-gear me-1"></i> 3. القيادة والإدارة ({{ $association->boardMembers->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-2 small" id="tab-members-tab" data-bs-toggle="tab" data-bs-target="#tab-members" type="button" role="tab">
                        <i class="fa-solid fa-users me-1"></i> 4. الأعضاء والمنتسبين
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-2 small" id="tab-financial-tab" data-bs-toggle="tab" data-bs-target="#tab-financial" type="button" role="tab">
                        <i class="fa-solid fa-coins me-1"></i> 5. الاقتصادية والمالية
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-2 small" id="tab-activities-tab" data-bs-toggle="tab" data-bs-target="#tab-activities" type="button" role="tab">
                        <i class="fa-solid fa-chart-line me-1"></i> 6. المشاريع والأنشطة ({{ $association->projects->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-2 small" id="tab-assets-tab" data-bs-toggle="tab" data-bs-target="#tab-assets" type="button" role="tab">
                        <i class="fa-solid fa-cubes me-1"></i> 7. الأصول والممتلكات
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-2 small" id="tab-loans-tab" data-bs-toggle="tab" data-bs-target="#tab-loans" type="button" role="tab">
                        <i class="fa-solid fa-hand-holding-dollar me-1"></i> 8. القروض البيضاء ({{ $association->loans->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-2 small" id="tab-evaluation-tab" data-bs-toggle="tab" data-bs-target="#tab-evaluation" type="button" role="tab">
                        <i class="fa-solid fa-clipboard-check me-1"></i> 9. المتابعة والتقييم
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="associationTabsContent">

                <!-- 1. البيانات الأساسية للجمعية -->
                <div class="tab-pane fade show active" id="tab-basic" role="tabpanel">
                    <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-circle-info me-2"></i> البيانات الأساسية والتأسيسية</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">الرقم التعريفي الفريد:</span>
                                <strong class="fs-6 font-monospace text-primary">{{ $association->code }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">الاسم الرسمي الكامل:</span>
                                <strong class="fs-6 text-dark">{{ $association->name }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">نوع وتصنيف الجمعية:</span>
                                <strong class="fs-6 text-dark">{{ $association->type }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">رقم الترخيص الرسمي:</span>
                                <strong class="text-dark">{{ $association->license_number ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">تاريخ الترخيص:</span>
                                <strong class="text-dark">{{ $association->license_date?->format('Y-m-d') ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">تاريخ التأسيس:</span>
                                <strong class="text-dark">{{ $association->founded_date?->format('Y-m-d') ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">تاريخ الإشهار:</span>
                                <strong class="text-dark">{{ $association->announced_date?->format('Y-m-d') ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">المحافظة:</span>
                                <strong class="text-dark">{{ $association->governorate?->name_ar }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">المديرية:</span>
                                <strong class="text-dark">{{ $association->directorate?->name_ar }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">العزلة / المركز:</span>
                                <strong class="text-dark">{{ $association->uzlah?->name_ar }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">القرية / الموقع الفرعي:</span>
                                <strong class="text-dark">{{ $association->subLocation?->name_ar ?? $association->address ?? '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">رقم الهاتف:</span>
                                <strong class="text-dark">{{ $association->phone ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">البريد الإلكتروني:</span>
                                <strong class="text-dark">{{ $association->email ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">رابط صفحة الجمعية:</span>
                                @if($association->website_url)
                                    <a href="{{ $association->website_url }}" target="_blank" class="text-primary text-decoration-none">
                                        {{ $association->website_url }} <i class="fa-solid fa-arrow-up-right-from-square small ms-1"></i>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. البيانات القانونية والتنظيمية -->
                <div class="tab-pane fade" id="tab-legal" role="tabpanel">
                    <h6 class="fw-bold text-success mb-3"><i class="fa-solid fa-scale-balanced me-2"></i> البيانات القانونية والتنظيمية للجمعية</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">الجهة المانحة للترخيص:</span>
                                <strong class="text-dark">{{ $association->issuing_authority ?: 'وزارة الشؤون الاجتماعية والعمل' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">حالة الترخيص:</span>
                                <strong class="text-dark">{{ $association->license_status ?: 'ساري المفعول' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">هل يوجد نظام أساسي معتمد؟</span>
                                <strong class="{{ $association->has_bylaws ? 'text-success' : 'text-danger' }}">
                                    <i class="fa-solid {{ $association->has_bylaws ? 'fa-check' : 'fa-xmark' }} me-1"></i>
                                    {{ $association->has_bylaws ? 'نعم' : 'لا' }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">هل يوجد سجل رسمي للأعضاء؟</span>
                                <strong class="{{ $association->has_member_registry ? 'text-success' : 'text-danger' }}">
                                    <i class="fa-solid {{ $association->has_member_registry ? 'fa-check' : 'fa-xmark' }} me-1"></i>
                                    {{ $association->has_member_registry ? 'نعم' : 'لا' }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">هل يوجد محضر تأسيسي رسمي؟</span>
                                <strong class="{{ $association->has_founding_minutes ? 'text-success' : 'text-danger' }}">
                                    <i class="fa-solid {{ $association->has_founding_minutes ? 'fa-check' : 'fa-xmark' }} me-1"></i>
                                    {{ $association->has_founding_minutes ? 'نعم' : 'لا' }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">هل يوجد مقر رسمي مخصص؟</span>
                                <strong class="{{ $association->has_headquarters ? 'text-success' : 'text-danger' }}">
                                    <i class="fa-solid {{ $association->has_headquarters ? 'fa-check' : 'fa-xmark' }} me-1"></i>
                                    {{ $association->has_headquarters ? 'نعم' : 'لا' }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">ملكية المقر:</span>
                                <strong class="text-dark">{{ $association->headquarters_ownership ?: 'إيجار' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">هل تعقد اجتماعات دورية؟</span>
                                <strong class="{{ $association->holds_regular_meetings ? 'text-success' : 'text-danger' }}">
                                    <i class="fa-solid {{ $association->holds_regular_meetings ? 'fa-check' : 'fa-xmark' }} me-1"></i>
                                    {{ $association->holds_regular_meetings ? 'نعم' : 'لا' }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">تاريخ آخر اجتماع جمعية عمومية:</span>
                                <strong class="text-dark">{{ $association->last_general_assembly_date?->format('Y-m-d') ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">تاريخ آخر انتخابات للهيئة الإدارية:</span>
                                <strong class="text-dark">{{ $association->last_election_date?->format('Y-m-d') ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">تاريخ آخر تحديث للبيانات:</span>
                                <strong class="text-dark">{{ $association->last_data_update_date?->format('Y-m-d') ?: $association->updated_at->format('Y-m-d') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. بيانات القيادة والإدارة -->
                <div class="tab-pane fade" id="tab-leadership" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-warning mb-0"><i class="fa-solid fa-users-gear me-2"></i> هيكل القيادة والإدارة وأعضاء الهيئة الإدارية</h6>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddBoardMember">
                            <i class="fa-solid fa-user-plus me-1"></i> إضافة عضو هيئة إدارية / قيادي
                        </button>
                    </div>

                    <!-- ملخص الهيكل الإداري -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="p-3 border rounded-3">
                                <span class="text-muted small d-block">رئيس الجمعية:</span>
                                <strong class="text-dark">{{ $association->president_name ?: '-' }}</strong>
                                <span class="d-block small text-muted font-monospace">{{ $association->president_phone }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded-3">
                                <span class="text-muted small d-block">نائب الرئيس:</span>
                                <strong class="text-dark">{{ $association->vice_president_name ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded-3">
                                <span class="text-muted small d-block">الأمين العام:</span>
                                <strong class="text-dark">{{ $association->secretary_general_name ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded-3">
                                <span class="text-muted small d-block">المسؤول المالي:</span>
                                <strong class="text-dark">{{ $association->financial_officer_name ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded-3">
                                <span class="text-muted small d-block">مسؤول الرقابة والتفتيش:</span>
                                <strong class="text-dark">{{ $association->oversight_officer_name ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded-3">
                                <span class="text-muted small d-block">المدير التنفيذي:</span>
                                <strong class="text-dark">{{ $association->executive_director_name ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded-3">
                                <span class="text-muted small d-block">عدد أعضاء الهيئة الإدارية:</span>
                                <strong class="text-dark">{{ $association->board_members_count }} عضو</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded-3">
                                <span class="text-muted small d-block">عدد أعضاء هيئة الرقابة:</span>
                                <strong class="text-dark">{{ $association->oversight_members_count }} عضو</strong>
                            </div>
                        </div>
                    </div>

                    <!-- جدول تفصيلي لأعضاء القيادة والإدارة -->
                    <h6 class="fw-bold text-dark mb-2">السجل التفصيلي لأعضاء القيادة والإدارة ({{ $association->boardMembers->count() }})</h6>
                    @if($association->boardMembers->isEmpty())
                        <div class="p-4 text-center bg-light rounded-3 text-muted">
                            <i class="fa-solid fa-user-group fs-2 mb-2 d-block"></i>
                            لم يتم إدراج أعضاء تفصيليين في الهيئة الإدارية بعد. اضغط على "إضافة عضو هيئة إدارية" بالأعلى لإدراج الأعضاء.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table-hasad align-middle mb-0 text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>الاسم الرباعي</th>
                                        <th>الصفة / المنصب</th>
                                        <th>الجنس</th>
                                        <th>تاريخ الميلاد / العمر (تلقائي)</th>
                                        <th>رقم البطاقة</th>
                                        <th>المؤهل والتخصص</th>
                                        <th>رقم الهاتف</th>
                                        <th>تاريخ التكليف</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($association->boardMembers as $member)
                                        <tr>
                                            <td class="fw-bold">{{ $member->full_name }}</td>
                                            <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $member->role_title }}</span></td>
                                            <td>{{ $member->gender }}</td>
                                            <td>
                                                {{ $member->birth_date?->format('Y-m-d') ?: '-' }}
                                                @if($member->age)
                                                    <span class="badge bg-secondary ms-1">({{ $member->age }} سنة)</span>
                                                @endif
                                            </td>
                                            <td class="font-monospace">{{ $member->national_id ?: '-' }}</td>
                                            <td>{{ $member->education_level ?: '-' }} {{ $member->specialization ? "({$member->specialization})" : '' }}</td>
                                            <td class="font-monospace">{{ $member->phone ?: '-' }}</td>
                                            <td>{{ $member->assignment_start_date?->format('Y-m-d') ?: '-' }}</td>
                                            <td>
                                                <span class="badge {{ $member->membership_status === 'نشط' ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $member->membership_status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- 4. بيانات الأعضاء والمنتسبين -->
                <div class="tab-pane fade" id="tab-members" role="tabpanel">
                    <h6 class="fw-bold text-info mb-3"><i class="fa-solid fa-users me-2"></i> بيانات الأعضاء والمنتسبين وقاعدة المستفيدين</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <span class="text-muted small d-block">إجمالي عدد الأعضاء</span>
                                <h3 class="fw-bold text-primary mb-0">{{ number_format($association->total_members) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <span class="text-muted small d-block">عدد الأعضاء الذكور</span>
                                <h3 class="fw-bold text-dark mb-0">{{ number_format($association->male_members) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <span class="text-muted small d-block">عدد العضوات الإناث</span>
                                <h3 class="fw-bold text-dark mb-0">{{ number_format($association->female_members) }}</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <span class="text-muted small d-block">الأعضاء الجدد خلال العام</span>
                                <h4 class="fw-bold text-success mb-0">{{ number_format($association->new_members_this_year) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <span class="text-muted small d-block">المستفيدين من خدمات الجمعية</span>
                                <h4 class="fw-bold text-info mb-0">{{ number_format($association->beneficiaries_count) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <span class="text-muted small d-block">الأسر المستفيدة</span>
                                <h4 class="fw-bold text-warning mb-0">{{ number_format($association->beneficiary_families_count) }}</h4>
                            </div>
                        </div>
                    </div>

                    <!-- جدول المزارعين المسجلين تحت هذه الجمعية (جلب تلقائي من سجل المزارعين العام) -->
                    <h6 class="fw-bold text-dark mb-2">المزارعون المنتسبون للجمعية من السجل العام ({{ $association->farmers->count() }})</h6>
                    @if($association->farmers->isEmpty())
                        <div class="p-3 text-center bg-light rounded-3 text-muted small">
                            لا يوجد مزارعون مسجلون تحت هذه الجمعية في سجل المزارعين حتى الآن.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0 text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>اسم المزارع</th>
                                        <th>الهوية</th>
                                        <th>الهاتف</th>
                                        <th>المساحة المزروعة</th>
                                        <th>المحصول الرئيسي</th>
                                        <th>مصدر الري</th>
                                        <th>العضوية</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($association->farmers as $farmer)
                                        <tr>
                                            <td class="fw-bold">{{ $farmer->farmer_name }}</td>
                                            <td>{{ $farmer->national_id ?: '-' }}</td>
                                            <td>{{ $farmer->phone ?: '-' }}</td>
                                            <td>{{ $farmer->cultivated_area }} {{ $farmer->area_unit }}</td>
                                            <td>{{ $farmer->main_crop_type ?: '-' }}</td>
                                            <td>{{ $farmer->irrigation_source }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $farmer->membership_status }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- 5. البيانات الاقتصادية والمالية -->
                <div class="tab-pane fade" id="tab-financial" role="tabpanel">
                    <h6 class="fw-bold text-success mb-3"><i class="fa-solid fa-coins me-2"></i> المؤشرات الاقتصادية والمالية للجمعية</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">رأس المال التأسيسي:</span>
                                <h5 class="fw-bold text-dark mb-0">{{ number_format($association->initial_capital) }} ر.ي</h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">رأس المال الحالي:</span>
                                <h5 class="fw-bold text-primary mb-0">{{ number_format($association->current_capital) }} ر.ي</h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">إجمالي قيمة الأسهم (محسوب تلقائياً):</span>
                                <h5 class="fw-bold text-success mb-0">{{ number_format($association->total_shares_value) }} ر.ي</h5>
                                <span class="small text-muted font-monospace">({{ number_format($association->shares_count) }} سهم × {{ number_format($association->share_value) }} ر.ي)</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">قيمة السهم الواحد:</span>
                                <strong>{{ number_format($association->share_value) }} ر.ي</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">الاشتراكات السنوية:</span>
                                <strong>{{ number_format($association->annual_subscription_fee) }} ر.ي</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">إجمالي قيمة الأصول:</span>
                                <strong>{{ number_format($association->total_assets_value ?: $association->estimated_assets_value) }} ر.ي</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">هل تمتلك حساباً بنكياً؟</span>
                                <strong class="{{ $association->has_bank_account ? 'text-success' : 'text-danger' }}">
                                    {{ $association->has_bank_account ? 'نعم تمتلك حساب بنكي' : 'لا' }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">هل تتوفر سجلات ودفاتر محاسبية رسمية؟</span>
                                <strong class="{{ $association->has_accounting_records ? 'text-success' : 'text-danger' }}">
                                    {{ $association->has_accounting_records ? 'نعم تتوفر سجلات محاسبية منتظمة' : 'لا' }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">تاريخ آخر ميزانية عمومية معتمدة:</span>
                                <strong>{{ $association->last_approved_budget_date?->format('Y-m-d') ?: 'لم تعتمد ميزانية حديثة' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. الأنشطة والمشاريع التعاونية (مع الانعكاس التلقائي) -->
                <div class="tab-pane fade" id="tab-activities" role="tabpanel">
                    <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-chart-line me-2"></i> المشاريع والأنشطة المنفذة والإنتاجية</h6>
                    
                    <!-- جداول الانعكاس التلقائي للمشاريع المربوطة -->
                    <div class="card border mb-3">
                        <div class="card-header bg-light py-2 fw-bold text-dark">
                            <i class="fa-solid fa-list-check text-success me-1"></i> المشاريع المسجلة باسم الجمعية ({{ $association->projects->count() }})
                        </div>
                        <div class="card-body p-0">
                            @if($association->projects->isEmpty())
                                <div class="p-3 text-center text-muted small">لا توجد مشاريع مسجلة حالياً لهذه الجمعية.</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0 text-nowrap">
                                        <thead class="table-light">
                                            <tr>
                                                <th>اسم المشروع</th>
                                                <th>النوع</th>
                                                <th>الحالة</th>
                                                <th>التكلفة (ر.ي)</th>
                                                <th>ملاحظات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($association->projects as $project)
                                                <tr>
                                                    <td class="fw-bold">{{ $project->project_name }}</td>
                                                    <td>{{ $project->project_type ?: '-' }}</td>
                                                    <td><span class="badge bg-primary">{{ $project->execution_status }}</span></td>
                                                    <td class="font-monospace">{{ number_format($project->cost) }}</td>
                                                    <td class="small text-muted">{{ $project->notes ?: '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- جداول المحاصيل المربوطة بالجمعية -->
                    <div class="card border mb-3">
                        <div class="card-header bg-light py-2 fw-bold text-dark">
                            <i class="fa-solid fa-wheat-awn text-warning me-1"></i> المحاصيل والإنتاج الزراعي التابع للجمعية ({{ $association->crops->count() }})
                        </div>
                        <div class="card-body p-0">
                            @if($association->crops->isEmpty())
                                <div class="p-3 text-center text-muted small">لا توجد محاصيل مسجلة حالياً لهذه الجمعية.</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0 text-nowrap">
                                        <thead class="table-light">
                                            <tr>
                                                <th>نوع المحصول</th>
                                                <th>الصنف</th>
                                                <th>كمية الإنتاج</th>
                                                <th>الوفرة</th>
                                                <th>تاريخ الزراعة والحصاد</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($association->crops as $crop)
                                                <tr>
                                                    <td class="fw-bold">{{ $crop->crop_type }}</td>
                                                    <td>{{ $crop->crop_variety ?: '-' }}</td>
                                                    <td class="font-monospace">{{ number_format($crop->production_quantity) }} {{ $crop->quantity_unit }}</td>
                                                    <td><span class="badge bg-success">{{ $crop->availability_status }}</span></td>
                                                    <td class="small">{{ $crop->planting_date?->format('Y-m-d') }} - {{ $crop->harvest_date?->format('Y-m-d') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- نصوص إضافية للأنشطة -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <span class="fw-bold d-block text-dark mb-1">الأنشطة الرئيسية:</span>
                                <p class="mb-0 text-muted small">{{ $association->main_activities ?: 'لم يتم تحديد أنشطة رئيسية بعد.' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <span class="fw-bold d-block text-dark mb-1">الأنشطة الزراعية والتسويقية:</span>
                                <p class="mb-0 text-muted small">{{ $association->agricultural_activities ?: 'لم يتم تحديد أنشطة زراعية بعد.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 7. الأصول والممتلكات -->
                <div class="tab-pane fade" id="tab-assets" role="tabpanel">
                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-cubes me-2"></i> سجل الأصول والممتلكات التقديرية</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">الأراضي الزراعية التابعة:</span>
                                <strong>{{ $association->agricultural_lands ?: 'لا توجد أراضي مسجلة' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">الآبار ومنشآت المياه:</span>
                                <strong>{{ $association->wells_water_structures ?: 'لا توجد آبار مسجلة' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">المعدات والآلات الزراعية:</span>
                                <strong>{{ $association->machinery_equipment ?: 'لا توجد معدات مسجلة' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">وسائل النقل:</span>
                                <strong>{{ $association->transport_vehicles ?: 'لا توجد وسائل نقل' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">المخازن ومراكز التسويق:</span>
                                <strong>{{ $association->warehouses ?: 'لا توجد مخازن' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">المباني والمقرات:</span>
                                <strong>{{ $association->buildings_headquarters ?: 'مقر مستأجر' }}</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small d-block">القيمة التقديرية الإجمالية للأصول والممتلكات:</span>
                                    <h4 class="fw-bold text-success mb-0">{{ number_format($association->estimated_assets_value) }} ر.ي</h4>
                                </div>
                                <span class="badge bg-success bg-opacity-10 text-success p-2">أصول معتمدة</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 8. سجل القروض البيضاء (بدون فوائد) -->
                <div class="tab-pane fade" id="tab-loans" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold text-warning mb-0"><i class="fa-solid fa-hand-holding-dollar me-2"></i> سجل القروض التمويلية البيضاء</h6>
                            <span class="small text-muted">إدارة القروض المستلمة والممنوحة مع الحساب التلقائي للأرصدة</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-warning fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#modalAddLoan">
                            <i class="fa-solid fa-plus me-1"></i> تسجيل قرض أبيض جديد
                        </button>
                    </div>

                    <!-- تنبيه رسمي مهم جداً -->
                    <div class="alert alert-warning border-warning border-opacity-50 py-3 mb-4 d-flex align-items-center">
                        <i class="fa-solid fa-circle-exclamation text-warning fs-3 me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-1">تنبيه شرعي وتنظيمي صارم:</h6>
                            <span class="small">كافة القروض الممنوحة والمستلمة عبر الجمعية هي <strong>قروض بيضاء بدون أي فوائد ربوية (0% فائدة)</strong>، مخصصة لدعم المزارعين والتمكين الاقتصادي والإنتاج الزراعي التعاوني.</span>
                        </div>
                    </div>

                    @if($association->loans->isEmpty())
                        <div class="p-4 text-center bg-light rounded-3 text-muted">
                            <i class="fa-solid fa-handshake-simple fs-2 mb-2 d-block"></i>
                            لا توجد قروض بيضاء مسجلة لهذه الجمعية حالياً. اضغط على الزر بالأعلى لإضافة أول قرض.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table-hasad align-middle mb-0 text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>رقم القرض</th>
                                        <th>النوع</th>
                                        <th>الجهة الممولة</th>
                                        <th>المستفيد</th>
                                        <th>مبلغ القرض</th>
                                        <th>المسدد</th>
                                        <th>الرصيد المتبقي (تلقائي)</th>
                                        <th>فترة السداد</th>
                                        <th>الحالة</th>
                                        <th>الغرض</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($association->loans as $loan)
                                        <tr>
                                            <td><span class="badge bg-secondary font-monospace">{{ $loan->code }}</span></td>
                                            <td>{{ $loan->loan_type }}</td>
                                            <td>{{ $loan->funding_entity }}</td>
                                            <td class="fw-bold">{{ $loan->beneficiary_name }}</td>
                                            <td class="font-monospace fw-bold">{{ number_format($loan->loan_amount) }} {{ $loan->currency }}</td>
                                            <td class="font-monospace text-success">{{ number_format($loan->paid_amount) }}</td>
                                            <td class="font-monospace text-danger fw-bold">{{ number_format($loan->remaining_balance) }}</td>
                                            <td>{{ $loan->repayment_period_months }} شهر (سماح {{ $loan->grace_period_months }} ش)</td>
                                            <td>
                                                @if($loan->remaining_balance <= 0)
                                                    <span class="badge bg-success">مسدد بالكامل</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">{{ $loan->loan_status }}</span>
                                                @endif
                                            </td>
                                            <td class="small text-muted">{{ $loan->purpose ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- 9. بيانات المتابعة والتقييم -->
                <div class="tab-pane fade" id="tab-evaluation" role="tabpanel">
                    <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-clipboard-check me-2"></i> تقارير المتابعة الميدانية والتقييم العام</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">مستوى النشاط التعاوني:</span>
                                <strong class="badge bg-success fs-6">{{ $association->cooperative_activity_level }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">الوضع الإداري:</span>
                                <strong class="text-dark">{{ $association->administrative_status }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">الوضع المالي:</span>
                                <strong class="text-dark">{{ $association->financial_status }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">التقييم العام للجمعية:</span>
                                <strong class="badge bg-primary fs-6">{{ $association->general_evaluation }}</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <span class="fw-bold text-dark d-block mb-1">أبرز الاحتياجات والصعوبات:</span>
                                <p class="mb-0 text-muted small">{{ $association->top_needs ?: 'لا توجد صعوبات ملحوظة حالياً.' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <span class="fw-bold text-dark d-block mb-1">التوصيات والدعم المطلوب:</span>
                                <p class="mb-0 text-muted small">{{ $association->recommendations ?: 'استمرار المتابعة والتيسير الميداني.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- مودال إضافة عضو قيادة وإدارة -->
<div class="modal fade" id="modalAddBoardMember" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow border-0 rounded-3">
            <div class="modal-header bg-light py-3">
                <h6 class="modal-title fw-bold text-dark">
                    <i class="fa-solid fa-user-plus text-primary me-2"></i> إضافة عضو هيئة إدارية / قيادي للجمعية
                </h6>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form method="POST" action="{{ route('associations.board_members.store', $association->id) }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted">الاسم الرباعي <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" placeholder="الاسم الرباعي الكامل" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold text-muted">الصفة / المنصب <span class="text-danger">*</span></label>
                            <select name="role_title" class="form-select" required>
                                <option value="رئيس الجمعية">رئيس الجمعية</option>
                                <option value="نائب الرئيس">نائب الرئيس</option>
                                <option value="أمين عام الجمعية">أمين عام الجمعية</option>
                                <option value="المسؤول المالي">المسؤول المالي</option>
                                <option value="مسؤول الرقابة والتفتيش">مسؤول الرقابة والتفتيش</option>
                                <option value="عضو هيئة إدارية">عضو هيئة إدارية</option>
                                <option value="عضو هيئة الرقابة">عضو هيئة الرقابة</option>
                                <option value="المدير التنفيذي">المدير التنفيذي</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold text-muted">الجنس <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="ذكر">ذكر</option>
                                <option value="أنثى">أنثى</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">تاريخ الميلاد (يُحسب العمر تلقائياً)</label>
                            <input type="date" name="birth_date" class="form-control">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">رقم البطاقة الشخصية</label>
                            <input type="text" name="national_id" class="form-control">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">رقم الهاتف</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">المؤهل التعليمي</label>
                            <input type="text" name="education_level" class="form-control" placeholder="بكالوريوس، ثانوية...">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">التخصص</label>
                            <input type="text" name="specialization" class="form-control" placeholder="زراعة، إدارة، محاسبة...">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">جهة التكليف</label>
                            <input type="text" name="assigning_entity" class="form-control" value="الجمعية العمومية">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-check me-1"></i> حفظ وإضافة العضو
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- مودال إضافة قرض أبيض (بدون فوائد) -->
<div class="modal fade" id="modalAddLoan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow border-0 rounded-3">
            <div class="modal-header bg-warning py-3">
                <h6 class="modal-title fw-bold text-dark">
                    <i class="fa-solid fa-hand-holding-dollar me-2"></i> تسجيل قرض أبيض جديد (بدون فوائد)
                </h6>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form method="POST" action="{{ route('associations.loans.store', $association->id) }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">نوع القرض <span class="text-danger">*</span></label>
                            <select name="loan_type" class="form-select" required>
                                <option value="ممنوح لمزارع/مجموعة">ممنوح لمزارع / مجموعة</option>
                                <option value="مستلم من جهة داعمة">مستلم من جهة داعمة</option>
                                <option value="تمكين اقتصادي">تمكين اقتصادي</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">الجهة الممولة <span class="text-danger">*</span></label>
                            <input type="text" name="funding_entity" class="form-control" value="صندوق تشجيع الإنتاج الزراعي والسمكي" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">المستفيد <span class="text-danger">*</span></label>
                            <input type="text" name="beneficiary_name" class="form-control" placeholder="اسم المستفيد أو المجموعة" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">مبلغ القرض الأبيض <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="loan_amount" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">العملة <span class="text-danger">*</span></label>
                            <select name="currency" class="form-select" required>
                                <option value="ريال يمني">ريال يمني</option>
                                <option value="دولار أمريكي">دولار أمريكي</option>
                                <option value="ريال سعودي">ريال سعودي</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">تاريخ الحصول على القرض</label>
                            <input type="date" name="received_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">مدة السداد (بالشهور) <span class="text-danger">*</span></label>
                            <input type="number" name="repayment_period_months" class="form-control" value="12" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">فترة السماح (بالشهور)</label>
                            <input type="number" name="grace_period_months" class="form-control" value="3">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">المبلغ المسدد حالياً (ر.ي)</label>
                            <input type="number" step="0.01" name="paid_amount" class="form-control" value="0">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted">حالة القرض <span class="text-danger">*</span></label>
                            <select name="loan_status" class="form-select" required>
                                <option value="ساري">ساري (قيد السداد)</option>
                                <option value="منتظم">منتظم في السداد</option>
                                <option value="متعثر">متعثر</option>
                                <option value="مسدد بالكامل">مسدد بالكامل</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted">الضمانات المقدمة</label>
                            <input type="text" name="collaterals" class="form-control" placeholder="ضمانة تجارية، عينية، جمعية...">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">الغرض من القرض الأبيض</label>
                            <textarea name="purpose" class="form-control" rows="2" placeholder="شراء بذور، منظومة طاقة شمسية، حراثة..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold">
                        <i class="fa-solid fa-check me-1"></i> تسجيل القرض في السجل
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
