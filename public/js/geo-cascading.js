/**
 * نظام حصاد - محرك الربط الجغرافي الديناميكي (Cascading Geographies Engine)
 */
function initCascadingGeography(options) {
    const govSelect = $(options.governorate);
    const distSelect = $(options.district);
    const subDistSelect = $(options.subDistrict);
    const villageSelect = $(options.village);

    // عند تغيير المحافظة
    if (govSelect.length && distSelect.length) {
        govSelect.on('change', function () {
            const govId = $(this).val();
            distSelect.html('<option value="">-- جارِ التحميل... --</option>').prop('disabled', true);
            if (subDistSelect.length) subDistSelect.html('<option value="">-- اختر العزلة --</option>').prop('disabled', true);
            if (villageSelect.length) villageSelect.html('<option value="">-- اختر القرية --</option>').prop('disabled', true);

            if (!govId) {
                distSelect.html('<option value="">-- اختر المديرية --</option>');
                return;
            }

            $.getJSON(`/geo/districts/${govId}`, function (data) {
                let optionsHtml = '<option value="">-- اختر المديرية --</option>';
                $.each(data, function (index, item) {
                    optionsHtml += `<option value="${item.id}">${item.name}</option>`;
                });
                distSelect.html(optionsHtml).prop('disabled', false);

                if (options.selectedDistrict) {
                    distSelect.val(options.selectedDistrict).trigger('change');
                    options.selectedDistrict = null;
                }
            }).fail(function () {
                distSelect.html('<option value="">-- خطأ في تحميل المديريات --</option>');
            });
        });
    }

    // عند تغيير المديرية
    if (distSelect.length && subDistSelect.length) {
        distSelect.on('change', function () {
            const distId = $(this).val();
            subDistSelect.html('<option value="">-- جارِ التحميل... --</option>').prop('disabled', true);
            if (villageSelect.length) villageSelect.html('<option value="">-- اختر القرية --</option>').prop('disabled', true);

            if (!distId) {
                subDistSelect.html('<option value="">-- اختر العزلة --</option>');
                return;
            }

            $.getJSON(`/geo/sub-districts/${distId}`, function (data) {
                let optionsHtml = '<option value="">-- اختر العزلة --</option>';
                $.each(data, function (index, item) {
                    optionsHtml += `<option value="${item.id}">${item.name}</option>`;
                });
                subDistSelect.html(optionsHtml).prop('disabled', false);

                if (options.selectedSubDistrict) {
                    subDistSelect.val(options.selectedSubDistrict).trigger('change');
                    options.selectedSubDistrict = null;
                }
            }).fail(function () {
                subDistSelect.html('<option value="">-- خطأ في تحميل العزل --</option>');
            });
        });
    }

    // عند تغيير العزلة
    if (subDistSelect.length && villageSelect.length) {
        subDistSelect.on('change', function () {
            const subDistId = $(this).val();
            villageSelect.html('<option value="">-- جارِ التحميل... --</option>').prop('disabled', true);

            if (!subDistId) {
                villageSelect.html('<option value="">-- اختر القرية --</option>');
                return;
            }

            $.getJSON(`/geo/villages/${subDistId}`, function (data) {
                let optionsHtml = '<option value="">-- اختر القرية --</option>';
                $.each(data, function (index, item) {
                    optionsHtml += `<option value="${item.id}">${item.name}</option>`;
                });
                villageSelect.html(optionsHtml).prop('disabled', false);

                if (options.selectedVillage) {
                    villageSelect.val(options.selectedVillage);
                    options.selectedVillage = null;
                }
            }).fail(function () {
                villageSelect.html('<option value="">-- خطأ في تحميل القرى --</option>');
            });
        });
    }

    // تشغيل تلقائي إذا كانت المحافظة محددة مسبقاً (في شاشات التعديل)
    if (govSelect.val()) {
        govSelect.trigger('change');
    }
}
