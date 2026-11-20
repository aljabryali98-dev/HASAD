<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>كشف وتقرير النطاق الجغرافي: {{ $currentGov?->name_ar ?? 'الجمهورية اليمنية' }} - نظام حصاد</title>
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
        .report-container {
            max-width: 1000px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 35px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px double #064E3B;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header-center {
            text-align: center;
        }
        .header-center h1 {
            font-size: 22px;
            font-weight: 800;
            color: #064E3B;
            margin-bottom: 4px;
        }
        .header-center h3 {
            font-size: 14px;
            color: #475569;
            font-weight: 600;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            margin-bottom: 25px;
        }
        .summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
        }
        .summary-card .label {
            font-size: 11px;
            color: #64748b;
            display: block;
            margin-bottom: 3px;
        }
        .summary-card .value {
            font-size: 15px;
            font-weight: 800;
            color: #064E3B;
        }
        .section-title {
            font-size: 15px;
            font-weight: 800;
            color: #064E3B;
            border-bottom: 2px solid #064E3B;
            padding-bottom: 5px;
            margin: 25px 0 12px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        .table-custom th, .table-custom td {
            border: 1px solid #cbd5e1;
            padding: 7px 9px;
            text-align: right;
        }
        .table-custom th {
            background-color: #f1f5f9;
            font-weight: 700;
            color: #1e293b;
        }
        .no-print-bar {
            max-width: 1000px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #064E3B;
            color: #ffffff;
            border: none;
            padding: 9px 20px;
            border-radius: 6px;
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
            padding: 9px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
        }
        .signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            text-align: center;
            margin-top: 35px;
            padding-top: 20px;
            border-top: 1px solid #cbd5e1;
            page-break-inside: avoid;
        }
        .signature-box p {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 45px;
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
            .report-container {
                border: none;
                box-shadow: none;
                padding: 0;
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <a href="{{ route('geo-explorer.index', ['gov_id' => $selectedGovId, 'dir_id' => $selectedDirId, 'uzl_id' => $selectedUzlId]) }}" class="btn-back">
            <i class="fa-solid fa-arrow-right"></i> العودة للمستكشف الجغرافي
        </a>
        <button onclick="window.print()" class="btn-print">
            <i class="fa-solid fa-print"></i> طباعة التقرير الشامل (PDF)
        </button>
    </div>

    <div class="report-container">
        <!-- ترويسة الوثيقة الرسمية -->
        <div class="header">
            <div style="font-size: 12px; line-height: 1.6;">
                <strong>الجمهورية اليمنية</strong><br>
                اللجنة الزراعية والسمكية العليا<br>
                النطاق الجغرافي: {{ $currentGov?->name_ar ?? 'كافة المحافظات' }}
                @if($currentDir) - مديرية {{ $currentDir->name_ar }} @endif
                @if($currentUzl) - عزلة {{ $currentUzl->name_ar }} @endif
            </div>
            <div class="header-center">
                <h1>كشف وحصر الأنشطة التنموية والزراعية</h1>
                <h3>الملف الإحصائي الميداني الموحد (Geo 360°)</h3>
                <span style="font-size: 12px; color: #064E3B; font-weight: bold; background: #ecfdf5; padding: 3px 12px; border-radius: 20px; border: 1px solid #a7f3d0; display: inline-block; margin-top: 4px;">
                    النطاق: {{ $currentUzl?->name_ar ?? $currentDir?->name_ar ?? $currentGov?->name_ar ?? 'الجمهورية' }}
                </span>
            </div>
            <div style="text-align: left; font-size: 11px; color: #64748b;">
                <strong>نظام حصاد ERP</strong><br>
                كود النطاق: {{ $currentUzl?->code ?? $currentDir?->code ?? $currentGov?->code ?? 'ALL' }}<br>
                التاريخ: {{ date('Y-m-d') }}
            </div>
        </div>

        <!-- ملخص المؤشرات الستة -->
        <div class="summary-grid">
            <div class="summary-card">
                <span class="label">الجمعيات التعاونية</span>
                <span class="value">{{ number_format($stats['associations_count']) }}</span>
            </div>
            <div class="summary-card">
                <span class="label">المجاميع الإنتاجية</span>
                <span class="value">{{ number_format($stats['productive_groups_count']) }}</span>
            </div>
            <div class="summary-card">
                <span class="label">الكوادر والفرسان</span>
                <span class="value">{{ number_format($stats['personnel_count']) }}</span>
            </div>
            <div class="summary-card">
                <span class="label">المشاريع والمبادرات</span>
                <span class="value">{{ number_format($stats['projects_count'] + $stats['initiatives_count']) }}</span>
            </div>
            <div class="summary-card">
                <span class="label">المزارعون المسجلون</span>
                <span class="value">{{ number_format($stats['farmers_count']) }}</span>
            </div>
            <div class="summary-card">
                <span class="label">القروض البيضاء</span>
                <span class="value">{{ number_format($stats['loans_count']) }}</span>
            </div>
        </div>

        <!-- 1. كشف الجمعيات التعاونية في هذا النطاق -->
        <div class="section-title">
            <i class="fa-solid fa-building-columns"></i> أولاً: كشف الجمعيات التعاونية المسجلة ({{ $associations->count() }})
        </div>
        @if($associations->isEmpty())
            <div style="font-size: 12px; color: #64748b; padding: 8px; background: #f8fafc; border: 1px dashed #cbd5e1; text-align: center; margin-bottom: 15px;">
                لا توجد جمعيات مسجلة في هذا النطاق.
            </div>
        @else
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>الكود</th>
                        <th>اسم الجمعية</th>
                        <th>النوع</th>
                        <th>المديرية / العزلة</th>
                        <th>رئيس الجمعية</th>
                        <th>إجمالي الأعضاء</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($associations as $a)
                        <tr>
                            <td style="font-family: monospace;">{{ $a->code }}</td>
                            <td><strong>{{ $a->name }}</strong></td>
                            <td>{{ $a->type }}</td>
                            <td>{{ $a->directorate?->name_ar }} - {{ $a->uzlah?->name_ar }}</td>
                            <td>{{ $a->president_name ?: '-' }}</td>
                            <td>{{ number_format($a->total_members) }}</td>
                            <td>{{ $a->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- 2. كشف الكوادر والفرسان في هذا النطاق -->
        <div class="section-title">
            <i class="fa-solid fa-graduation-cap"></i> ثانياً: كشف الكوادر والفرسان والتدريب ({{ $personnel->count() }})
        </div>
        @if($personnel->isEmpty())
            <div style="font-size: 12px; color: #64748b; padding: 8px; background: #f8fafc; border: 1px dashed #cbd5e1; text-align: center; margin-bottom: 15px;">
                لا توجد كوادر مسجلة في هذا النطاق.
            </div>
        @else
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>الكود</th>
                        <th>الاسم الرباعي</th>
                        <th>الفئة والتخصص</th>
                        <th>رقم الهاتف</th>
                        <th>الجمعية التابع لها</th>
                        <th>الحالة الميدانية</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($personnel->take(25) as $p)
                        <tr>
                            <td style="font-family: monospace;">{{ $p->code }}</td>
                            <td><strong>{{ $p->full_name }}</strong></td>
                            <td>{{ $p->category }} @if($p->sub_specialty) - {{ $p->sub_specialty }} @endif</td>
                            <td>{{ $p->phone ?: '-' }}</td>
                            <td>{{ $p->association?->name ?: '-' }}</td>
                            <td>{{ $p->is_activated_in_field ? 'مفعل' : 'مدرج' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if($personnel->count() > 25)
                <div style="font-size: 11px; color: #64748b; text-align: center; margin-top: -10px; margin-bottom: 15px;">
                    * معروض أول 25 كادر من إجمالي {{ $personnel->count() }} كادر في هذا النطاق.
                </div>
            @endif
        @endif

        <!-- 3. كشف المجاميع الإنتاجية والمشاريع -->
        <div class="section-title">
            <i class="fa-solid fa-people-roof"></i> ثالثاً: المجاميع الإنتاجية والمشاريع التنموية
        </div>
        <table class="table-custom">
            <thead>
                <tr>
                    <th>البيان</th>
                    <th>العدد الكلي</th>
                    <th>القيم / التكلفة الإجمالية</th>
                    <th>المستفيدين / الأعضاء</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>المجاميع الإنتاجية</strong></td>
                    <td>{{ number_format($productiveGroups->count()) }} مجموعة</td>
                    <td>-</td>
                    <td>{{ number_format($productiveGroups->sum('total_members')) }} عضو</td>
                </tr>
                <tr>
                    <td><strong>المشاريع المنفذة</strong></td>
                    <td>{{ number_format($projects->count()) }} مشروع</td>
                    <td>{{ number_format($projects->sum('cost')) }} ر.ي</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td><strong>المبادرات المجتمعية</strong></td>
                    <td>{{ number_format($initiatives->count()) }} مبادرة</td>
                    <td>{{ number_format($initiatives->sum('cost')) }} ر.ي</td>
                    <td>{{ number_format($initiatives->sum('beneficiaries_count')) }} مستفيد</td>
                </tr>
                <tr>
                    <td><strong>القروض البيضاء التنموية</strong></td>
                    <td>{{ number_format($loans->count()) }} قرض</td>
                    <td>{{ number_format($loans->sum('loan_amount')) }} ر.ي</td>
                    <td>-</td>
                </tr>
            </tbody>
        </table>

        <!-- التواقيع والاعتماد الرسمي -->
        <div class="signatures">
            <div class="signature-box">
                <p>منسق النطاق الميداني</p>
                <div class="sign-line">التوقيع والاسم</div>
            </div>
            <div class="signature-box">
                <p>إدارة الإحصاء والمتابعة</p>
                <div class="sign-line">ختم الاعتماد</div>
            </div>
            <div class="signature-box">
                <p>مدير فرع الهيئة / المحافظة</p>
                <div class="sign-line">الاعتماد النهائي</div>
            </div>
        </div>

        <div style="margin-top: 30px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px dashed #e2e8f0; padding-top: 10px;">
            تم استخراج وطباعة هذا التقرير رسمياً عبر المنظومة الوطنية الموحدة للرصد والتقييم والتحليل التعاوني الزراعي - نظام حصاد ERP
        </div>
    </div>

</body>
</html>
