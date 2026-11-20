<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>سند مساهمة واكتتاب - {{ $shareholder->full_name }} - {{ $association->name }}</title>
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
            background-color: #f1f5f9;
            color: #0f172a;
            padding: 30px;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border: 2px solid #064E3B;
            border-radius: 12px;
            padding: 35px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            position: relative;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 80px;
            font-weight: 900;
            color: rgba(6, 78, 59, 0.04);
            pointer-events: none;
            white-space: nowrap;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .header-title {
            text-align: center;
            flex-grow: 1;
        }
        .header-title h2 {
            font-size: 22px;
            font-weight: 800;
            color: #064E3B;
            margin-bottom: 4px;
        }
        .header-title h4 {
            font-size: 15px;
            color: #475569;
            font-weight: 600;
        }
        .receipt-badge {
            display: inline-block;
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            padding: 6px 16px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 14px;
            margin-top: 10px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 25px;
            font-size: 14px;
        }
        .meta-item {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 6px;
        }
        .meta-item span.label {
            color: #64748b;
        }
        .meta-item span.value {
            font-weight: 700;
            color: #1e293b;
        }
        .receipt-content {
            font-size: 15px;
            line-height: 2;
            color: #334155;
            margin-bottom: 30px;
            padding: 0 10px;
        }
        .amount-box {
            background: #f0fdf4;
            border: 2px dashed #059669;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
        }
        .amount-box .num {
            font-size: 26px;
            font-weight: 900;
            color: #065f46;
        }
        .signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .signature-box p {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 45px;
        }
        .signature-box .sign-line {
            font-weight: 700;
            color: #1e293b;
            border-top: 1px solid #94a3b8;
            padding-top: 6px;
        }
        .no-print-bar {
            max-width: 800px;
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
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .no-print-bar {
                display: none !important;
            }
            .receipt-container {
                border: 2px solid #000000;
                box-shadow: none;
                margin: 0;
                width: 100%;
                max-width: 100%;
                padding: 25px;
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
            <i class="fa-solid fa-print"></i> طباعة السند الرسمي
        </button>
    </div>

    <div class="receipt-container">
        <div class="watermark">نظام حصاد التعاوني</div>

        <div class="header">
            <div style="text-align: right; font-size: 12px; color: #475569;">
                <strong>الجمهورية اليمنية</strong><br>
                اللجنة الزراعية والسمكية العليا<br>
                محافظة {{ $association->governorate?->name_ar }}<br>
                مديرية {{ $association->directorate?->name_ar }}
            </div>

            <div class="header-title">
                <h2>{{ $association->name }}</h2>
                <h4>كود الجمعية: {{ $association->code }} | ترخيص: {{ $association->license_number ?: 'معتمد' }}</h4>
                <div class="receipt-badge">
                    <i class="fa-solid fa-receipt me-1"></i> سند قبض واكتتاب مساهم رسمي
                </div>
            </div>

            <div style="text-align: left; font-size: 12px; color: #475569;">
                <strong>رقم السند:</strong> {{ $shareholder->receipt_number }}<br>
                <strong>رقم المساهم:</strong> {{ $shareholder->shareholder_number }}<br>
                <strong>التاريخ:</strong> {{ $shareholder->join_date?->format('Y-m-d') ?? date('Y-m-d') }}
            </div>
        </div>

        <div class="meta-grid">
            <div class="meta-item">
                <span class="label">اسم المساهم الكامل:</span>
                <span class="value">{{ $shareholder->full_name }}</span>
            </div>
            <div class="meta-item">
                <span class="label">الرقم الوطني / الهوية:</span>
                <span class="value">{{ $shareholder->national_id ?: 'غير مسجل' }}</span>
            </div>
            <div class="meta-item">
                <span class="label">رقم الهاتف:</span>
                <span class="value">{{ $shareholder->phone ?: 'غير مسجل' }}</span>
            </div>
            <div class="meta-item">
                <span class="label">الجنس:</span>
                <span class="value">{{ $shareholder->gender }}</span>
            </div>
            <div class="meta-item">
                <span class="label">عدد الأسهم المكتتب بها:</span>
                <span class="value">{{ number_format($shareholder->shares_count) }} سهم</span>
            </div>
            <div class="meta-item">
                <span class="label">قيمة السهم الواحد:</span>
                <span class="value">{{ number_format($shareholder->share_value) }} ر.ي</span>
            </div>
            <div class="meta-item">
                <span class="label">إجمالي قيمة الاكتتاب:</span>
                <span class="value">{{ number_format($shareholder->total_subscription_amount) }} ر.ي</span>
            </div>
            <div class="meta-item">
                <span class="label">المبلغ المتبقي:</span>
                <span class="value">{{ number_format($shareholder->remaining_amount) }} ر.ي</span>
            </div>
        </div>

        <div class="receipt-content">
            تشهد إدارة <strong>{{ $association->name }}</strong> بأن الأخ/الأخت: <strong>{{ $shareholder->full_name }}</strong> قد اكتتب(ت) رسمياً بعدد <strong>({{ $shareholder->shares_count }}) سهم</strong> في رأس مال الجمعية، وقد سدد(ت) المبلغ الموضح أدناه كدفعة مساهمة، وله(ا) كافة الحقوق والواجبات المقررة في النظام الأساسي للجمعيات التعاونية الزراعية.
        </div>

        <div class="amount-box">
            <div style="font-size: 13px; color: #047857; margin-bottom: 4px; font-weight: 600;">المبلغ المقبوض الفعلي:</div>
            <div class="num">{{ number_format($shareholder->paid_amount) }} <span style="font-size: 16px;">ريال يمني</span></div>
            @if($shareholder->notes)
                <div style="font-size: 12px; color: #475569; margin-top: 6px;">ملاحظات: {{ $shareholder->notes }}</div>
            @endif
        </div>

        <div class="signatures">
            <div class="signature-box">
                <p>المستلم / المحاسب</p>
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

        <div style="margin-top: 25px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px dashed #e2e8f0; padding-top: 10px;">
            تم إصدار هذا السند إلكترونياً عبر المنظومة الوطنية الموحدة للرصد والتقييم والتحليل التعاوني الزراعي (نظام حصاد ERP)
        </div>
    </div>

</body>
</html>
