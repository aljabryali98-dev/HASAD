@extends('layouts.app')

@section('title', 'تأسيس وتسجيل جمعية جديدة - نظام حصاد')

@section('content')
<div class="container-fluid p-0">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <span class="text-success fs-5"><i class="fa-solid fa-plus-circle"></i></span>
                تأسيس وتسجيل جمعية تعاونية جديدة
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small text-muted">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('associations.index') }}" class="text-decoration-none text-muted">الجمعيات التعاونية</a></li>
                    <li class="breadcrumb-item active text-success fw-bold" aria-current="page">تسجيل جمعية جديدة</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('associations.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2 shadow-sm">
                <i class="fa-solid fa-arrow-right"></i>
                <span>العودة لقائمة الجمعيات</span>
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <h6 class="fw-bold mb-2 d-flex align-items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>يرجى مراجعة وتصحيح الحقول التالية:</span>
            </h6>
            <ul class="mb-0 small ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('associations.store') }}">
        @csrf

        <!-- 1. النطاق الجغرافي والبيانات الأساسية -->
        <div class="card-hasad mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center gap-2">
                <i class="fa-solid fa-map-location-dot text-success fs-5"></i>
                <h6 class="fw-bold mb-0 text-dark">أولاً: النطاق الجغرافي والبيانات الأساسية للجمعية</h6>
            </div>
            <div class="card-body p-4">
                <div class="mb-4 p-3 bg-light rounded-3 border">
                    <h6 class="fw-bold text-dark mb-3 small"><i class="fa-solid fa-location-crosshairs text-success me-1"></i> التوزيع الإداري والجغرافي <span class="text-danger">*</span></h6>
                    <x-location-hierarchy-selector 
                        :required="true"
                        :governorateId="old('governorate_id')"
                        :directorateId="old('directorate_id')"
                        :uzlahId="old('uzlah_id')"
                        :subLocationId="old('sub_location_id')"
                        :showAddButton="true"
                    />
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-dark">اسم الجمعية الرسمي <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="مثال: جمعية سنحان التعاونية الزراعية لمنتجي الحبوب" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-dark">نوع الجمعية <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="زراعية" {{ old('type') == 'زراعية' ? 'selected' : '' }}>زراعية</option>
                            <option value="متعددة الأغراض" {{ old('type') == 'متعددة الأغراض' ? 'selected' : '' }}>متعددة الأغراض</option>
                            <option value="تسويقية" {{ old('type') == 'تسويقية' ? 'selected' : '' }}>تسويقية</option>
                            <option value="حيوانية ونحلية" {{ old('type') == 'حيوانية ونحلية' ? 'selected' : '' }}>حيوانية ونحلية</option>
                            <option value="حرفية وإنتاجية" {{ old('type') == 'حرفية وإنتاجية' ? 'selected' : '' }}>حرفية وإنتاجية</option>
                            <option value="استهلاكية" {{ old('type') == 'استهلاكية' ? 'selected' : '' }}>استهلاكية</option>
                            <option value="خدمية" {{ old('type') == 'خدمية' ? 'selected' : '' }}>خدمية</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-dark">حالة الجمعية <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="نشطة" {{ old('status') == 'نشطة' ? 'selected' : '' }}>نشطة</option>
                            <option value="تحت التأسيس" {{ old('status') == 'تحت التأسيس' ? 'selected' : '' }}>تحت التأسيس</option>
                            <option value="متوقفة" {{ old('status') == 'متوقفة' ? 'selected' : '' }}>متوقفة</option>
                            <option value="متعثرة" {{ old('status') == 'متعثرة' ? 'selected' : '' }}>متعثرة</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-muted">عنوان المقر الرئيسي تفصيلاً</label>
                        <input type="text" name="address" class="form-control" placeholder="القرية، الشارع، المبنى..." value="{{ old('address') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-muted">رقم هاتف الجمعية</label>
                        <input type="text" name="phone" class="form-control font-monospace" placeholder="770000000" value="{{ old('phone') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-muted">البريد الإلكتروني للجمعية</label>
                        <input type="email" name="email" class="form-control" placeholder="info@association.org" value="{{ old('email') }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. البيانات القانونية والتنظيمية -->
        <div class="card-hasad mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center gap-2">
                <i class="fa-solid fa-scale-balanced text-success fs-5"></i>
                <h6 class="fw-bold mb-0 text-dark">ثانياً: البيانات القانونية والتنظيمية والتراخيص</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted">رقم الترخيص</label>
                        <input type="text" name="license_number" class="form-control font-monospace" placeholder="مثال: 124/2020" value="{{ old('license_number') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted">تاريخ الترخيص</label>
                        <input type="date" name="license_date" class="form-control" value="{{ old('license_date') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted">تاريخ التأسيس</label>
                        <input type="date" name="founded_date" class="form-control" value="{{ old('founded_date') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted">تاريخ الإشهار</label>
                        <input type="date" name="announced_date" class="form-control" value="{{ old('announced_date') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">الجهة المانحة للترخيص</label>
                        <input type="text" name="issuing_authority" class="form-control" placeholder="وزارة الشؤون الاجتماعية والعمل..." value="{{ old('issuing_authority') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">رابط صفحة أو موقع الجمعية</label>
                        <input type="url" name="website_url" class="form-control" placeholder="https://..." value="{{ old('website_url') }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. بيانات القيادة والإدارة -->
        <div class="card-hasad mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center gap-2">
                <i class="fa-solid fa-users-gear text-success fs-5"></i>
                <h6 class="fw-bold mb-0 text-dark">ثالثاً: الهيكل القيادي والإداري للجمعية</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-dark">اسم رئيس الجمعية</label>
                        <input type="text" name="president_name" class="form-control" placeholder="الاسم الرباعي لرئيس الجمعية" value="{{ old('president_name') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-muted">رقم هاتف الرئيس</label>
                        <input type="text" name="president_phone" class="form-control font-monospace" placeholder="770000000" value="{{ old('president_phone') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-muted">اسم نائب الرئيس</label>
                        <input type="text" name="vice_president_name" class="form-control" placeholder="الاسم الرباعي" value="{{ old('vice_president_name') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted">اسم أمين عام الجمعية</label>
                        <input type="text" name="secretary_general_name" class="form-control" value="{{ old('secretary_general_name') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted">اسم المسؤول المالي</label>
                        <input type="text" name="financial_officer_name" class="form-control" value="{{ old('financial_officer_name') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted">مسؤول الرقابة والتفتيش</label>
                        <input type="text" name="oversight_officer_name" class="form-control" value="{{ old('oversight_officer_name') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted">اسم المدير التنفيذي</label>
                        <input type="text" name="executive_director_name" class="form-control" value="{{ old('executive_director_name') }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. بيانات الأعضاء والمالية -->
        <div class="card-hasad mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center gap-2">
                <i class="fa-solid fa-coins text-success fs-5"></i>
                <h6 class="fw-bold mb-0 text-dark">رابعاً: بيانات الأعضاء ورأس المال والأسهم</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-dark">إجمالي عدد الأعضاء</label>
                        <input type="number" name="total_members" class="form-control stat-num" min="0" value="{{ old('total_members', 0) }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted">الأعضاء الذكور</label>
                        <input type="number" name="male_members" class="form-control stat-num" min="0" value="{{ old('male_members', 0) }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted">العضوات الإناث</label>
                        <input type="number" name="female_members" class="form-control stat-num" min="0" value="{{ old('female_members', 0) }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted">المستفيدين من الخدمات</label>
                        <input type="number" name="beneficiaries_count" class="form-control stat-num" min="0" value="{{ old('beneficiaries_count', 0) }}">
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted">رأس المال التأسيسي (ر.ي)</label>
                        <input type="number" step="0.01" name="initial_capital" class="form-control stat-num" value="{{ old('initial_capital', 0) }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted">رأس المال الحالي (ر.ي)</label>
                        <input type="number" step="0.01" name="current_capital" class="form-control stat-num" value="{{ old('current_capital', 0) }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted">قيمة السهم الواحد (ر.ي)</label>
                        <input type="number" step="0.01" name="share_value" class="form-control stat-num" value="{{ old('share_value', 1000) }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted">عدد الأسهم المصدرة</label>
                        <input type="number" name="shares_count" class="form-control stat-num" value="{{ old('shares_count', 0) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- أزرار الإجراءات -->
        <div class="d-flex justify-content-end gap-3 mb-5">
            <a href="{{ route('associations.index') }}" class="btn btn-secondary px-4">إلغاء</a>
            <button type="submit" class="btn btn-hasad-primary px-5 py-2 fw-bold shadow-sm">
                <i class="fa-solid fa-check me-2"></i> تأسيس وتسجيل الجمعية في النظام
            </button>
        </div>
    </form>
</div>
@endsection
