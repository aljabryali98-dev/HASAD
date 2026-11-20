<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>الملف الرسمي الشامل: {{ $association->name }} - نظام حصاد</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
            margin: 0;
            padding: 0;
        }
        body {
            background-color: #f8fafc;
            color: #0f172a;
            padding: 25px;
        }
        .dossier-container {
            max-width: 960px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px double #064E3B;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .header-center {
            text-align: center;
        }
        .header-center h1 {
            font-size: 24px;
            font-weight: 800;
            color: #064E3B;
            margin-bottom: 5px;
        }
        .header-center h3 {
            font-size: 14px;
            color: #475569;
            font-weight: 600;
        }
        .qr-placeholder {
            border: 2px solid #064E3B;
            padding: 8px;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            color: #064E3B;
            width: 85px;
            height: 85px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-radius: 6px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 25px;
        }
        .summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }
        .summary-card .label {
            font-size: 12px;
            color: #64748b;
            display: block;
            margin-bottom: 4px;
        }
        .summary-card .value {
            font-size: 16px;
            font-weight: 800;
            color: #064E3B;
        }
        .section-title {
            font-size: 16px;
            font-weight: 800;
            color: #064E3B;
            border-bottom: 2px solid #064E3B;
            padding-bottom: 6px;
            margin: 25px 0 15px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .info-table th, .info-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: right;
        }
        .info-table th {
            background-color: #f1f5f9;
            font-weight: 700;
            color: #1e293b;
        }
        .no-print-bar {
            max-width: 960px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #064E3B;
            color: #ffffff;
            border: none;
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-back {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
        }
        .signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #cbd5e1;
            page-break-inside: avoid;
        }
        .signature-box p {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 50px;
        }
        .signature-box .sign-line {
            font-weight: 700;
            color: #1e293b;
            border-top: 1px solid #94a3b8;
            padding-top: 6px;
            font-size: 13px;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .no-print-bar {
                display: none !important;
            }
            .dossier-container {
                border: none;
                box-shadow: none;
                padding: 10px;
                width: 100%;
                max-width: 100%;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <a href="{{ route('associations.show', $association->id) }}" class="btn-back">
            <i class="fa-solid fa-arrow-right"></i> العودة لملف الجمعية
        </a>
        <button onclick="window.print()" class="btn-print">
            <i class="fa-solid fa-print"></i> طباعة الملف الشامل (PDF)
        </button>
    </div>

    <div class="dossier-container">
        <!-- ترويسة الوثيقة الرسمية -->
        <div class="header">
            <div style="font-size: 12px; line-height: 1.6;">
                <strong>الجمهورية اليمنية</strong><br>
                اللجنة الزراعية والسمكية العليا<br>
                الاتحاد التعاوني الزراعي<br>
                محافظة: {{ $association->governorate?->name_ar }}
            </div>
            <div class="header-center">
                <h1>{{ $association->name }}</h1>
                <h3>الملف التعريفي والتشغيلي الشامل (Dossier 360°)</h3>
                <span style="font-size: 12px; color: #064E3B; font-weight: bold; background: #ecfdf5; padding: 3px 12px; border-radius: 20px; border: 1px solid #a7f3d0; display: inline-block; margin-top: 5px;">
                    كود: {{ $association->code }} | الحالة: {{ $association->status }}
                </span>
            </div>
            <div style="text-align: left;">
                <div class="qr-placeholder">
                    <i class="fa-solid fa-qrcode" style="font-size: 32px; margin-bottom: 2px;"></i>
                    <span>تحقق رقمي</span>
                </div>
                <div style="font-size: 10px; color: #64748b; margin-top: 4px;">تاريخ الطباعة: {{ date('Y-m-d') }}</div>
            </div>
        </div>

        <!-- المؤشرات العامة للجمعية -->
        <div class="summary-grid">
            <div class="summary-card">
                <span class="label">إجمالي الأعضاء والمساهمين</span>
                <span class="value">{{ number_format($association->total_members) }} عضو</span>
            </div>
            <div class="summary-card">
                <span class="label">رأس المال الحالي</span>
                <span class="value">{{ number_format($association->current_capital) }} ر.ي</span>
            </div>
            <div class="summary-card">
                <span class="label">الكوادر والفرسان</span>
                <span class="value">{{ number_format($association->personnel->count()) }} كادر</span>
            </div>
            <div class="summary-card">
                <span class="label">المشاريع والمبادرات</span>
                <span class="value">{{ number_format($association->projects->count()) }} مشروع</span>
            </div>
        </div>

        <!-- 1. البيانات التأسيسية والمكانية -->
        <div class="section-title">
            <i class="fa-solid fa-circle-info"></i> أولاً: البيانات الأساسية والمكانية
        </div>
        <table class="info-table">
            <tr>
                <th style="width: 20%;">الاسم الرسمي</th>
                <td style="width: 30%;">{{ $association->name }}</td>
                <th style="width: 20%;">الكود الرقمي الفريد</th>
                <td style="width: 30%;">{{ $association->code }}</td>
            </tr>
            <tr>
                <th>رقم وتاريخ الترخيص</th>
                <td>{{ $association->license_number ?: 'غير مسجل' }} ({{ $association->license_date?->format('Y-m-d') ?: '-' }})</td>
                <th>تاريخ التأسيس والإشهار</th>
                <td>{{ $association->founded_date?->format('Y-m-d') ?: '-' }} (إشهار: {{ $association->announced_date?->format('Y-m-d') ?: '-' }})</td>
            </tr>
            <tr>
                <th>النطاق الجغرافي</th>
                <td>{{ $association->governorate?->name_ar }} - مديرية {{ $association->directorate?->name_ar }}</td>
                <th>العزلة والقرية</th>
                <td>عزلة {{ $association->uzlah?->name_ar }} @if($association->subLocation) - قرية {{ $association->subLocation->name_ar }} @endif</td>
            </tr>
            <tr>
                <th>العنوان الميداني ورقم الهاتف</th>
                <td>{{ $association->address ?: 'المقر الرئيسي' }}</td>
                <td>{{ $association->phone ?: 'غير مدون' }}</td>
            </tr>
        </table>

        <!-- 2. الهيئة الإدارية والقيادة -->
        <div class="section-title">
            <i class="fa-solid fa-users-gear"></i> ثانياً: الهيئة الإدارية والرقابية المعتمدة
        </div>
        <table class="info-table">
            <thead>
                <tr>
                    <th>المنصب</th>
                    <th>الاسم الرباعي</th>
                    <th>رقم الهاتف</th>
                    <th>المؤهل العلمي</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>رئيس الجمعية</strong></td>
                    <td>{{ $association->president_name ?: '-' }}</td>
                    <td>{{ $association->president_phone ?: '-' }}</td>
                    <td>-</td>
                    <td>نشط</td>
                </tr>
                @foreach($association->boardMembers as $bm)
                    @if($bm->role_title !== 'رئيس الجمعية')
                        <tr>
                            <td>{{ $bm->role_title }}</td>
                            <td>{{ $bm->full_name }}</td>
                            <td>{{ $bm->phone ?: '-' }}</td>
                            <td>{{ $bm->education_level ?: '-' }}</td>
                            <td>{{ $bm->membership_status }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

        <!-- 3. كشف المساهمين المسجلين حديثاً -->
        <div class="section-title">
            <i class="fa-solid fa-users"></i> ثالثاً: كشف المساهمين والاكتتاب
        </div>
        @if($association->shareholders->isEmpty())
            <div style="font-size: 13px; color: #64748b; padding: 10px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 4px; text-align: center;">
                لم يتم إدراج مساهمين تفصيليين عبر النظام بعد. إجمالي المسجلين في السجل العام: {{ number_format($association->total_members) }} مساهم.
            </div>
        @else
            <table class="info-table">
                <thead>
                    <tr>
                        <th>رقم المساهم</th>
                        <th>الاسم الرباعي</th>
                        <th>عدد الأسهم</th>
                        <th>قيمة الاكتتاب</th>
                        <th>المبلغ المسدد</th>
                        <th>المتبقي</th>
                        <th>تاريخ الانضمام</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($association->shareholders as $sh)
                        <tr>
                            <td style="font-family: monospace;">{{ $sh->shareholder_number }}</td>
                            <td><strong>{{ $sh->full_name }}</strong></td>
                            <td>{{ number_format($sh->shares_count) }}</td>
                            <td>{{ number_format($sh->total_subscription_amount) }} ر.ي</td>
                            <td>{{ number_format($sh->paid_amount) }} ر.ي</td>
                            <td>{{ number_format($sh->remaining_amount) }} ر.ي</td>
                            <td>{{ $sh->join_date?->format('Y-m-d') ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- 4. كشف فرسان الجمعية والكوادر الميدانية -->
        <div class="section-title">
            <i class="fa-solid fa-graduation-cap"></i> رابعاً: كشف فرسان الجمعية والكوادر الميدانية ({{ $association->personnel->count() }} كادر)
        </div>
        @if($association->personnel->isEmpty())
            <div style="font-size: 13px; color: #64748b; padding: 10px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 4px; text-align: center;">
                لا توجد كوادر أو فرسان مربوطين مباشرة بهذه الجمعية حتى الآن.
            </div>
        @else
            <table class="info-table">
                <thead>
                    <tr>
                        <th>الكود</th>
                        <th>الاسم الرباعي</th>
                        <th>الفئة / التخصص</th>
                        <th>رقم الهاتف</th>
                        <th>المؤهل</th>
                        <th>الحالة الميدانية</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($association->personnel->take(15) as $p)
                        <tr>
                            <td style="font-family: monospace;">{{ $p->code }}</td>
                            <td><strong>{{ $p->full_name }}</strong></td>
                            <td>{{ $p->category }} @if($p->sub_specialty) - {{ $p->sub_specialty }} @endif</td>
                            <td>{{ $p->phone ?: '-' }}</td>
                            <td>{{ $p->qualification ?: '-' }}</td>
                            <td>{{ $p->is_activated_in_field ? 'مفعل ميدانياً' : 'مدرج' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if($association->personnel->count() > 15)
                <div style="font-size: 11px; color: #64748b; text-align: center; margin-top: -10px; margin-bottom: 15px;">
                    * معروض أول 15 كادر من إجمالي {{ $association->personnel->count() }} كادر مسجل.
                </div>
            @endif
        @endif

        <!-- التواقيع والاعتماد الرسمي -->
        <div class="signatures">
            <div class="signature-box">
                <p>المسؤول المالي</p>
                <div class="sign-line">{{ $association->financial_officer_name ?: 'المسؤول المالي' }}</div>
            </div>
            <div class="signature-box">
                <p>ختم الجمعية الرسمي</p>
                <div class="sign-line">ختم الاعتماد</div>
            </div>
            <div class="signature-box">
                <p>رئيس مجلس الإدارة</p>
                <div class="sign-line">{{ $association->president_name ?: 'رئيس الجمعية' }}</div>
            </div>
        </div>

        <div style="margin-top: 30px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px dashed #e2e8f0; padding-top: 10px;">
            تم استخراج وطباعة هذا التقرير رسمياً عبر المنظومة الوطنية الموحدة للرصد والتقييم والتحليل التعاوني الزراعي - نظام حصاد ERP
        </div>
    </div>

</body>
</html>
