import React, { useState, useEffect, useCallback } from 'react';

/**
 * LocationHierarchySelector - مكون القوائم المنسدلة الرباعية الذكية (React)
 * 1. المحافظة -> 2. المديرية -> 3. العزلة/المركز -> 4. القرية/المنطقة الفرعية/الجمعية
 * مزود بزر إضافة سريع (+ إضافة جديد) ونافذة مصغرة (Inline Modal)
 */
export default function LocationHierarchySelector({
    value = {}, // { governorate_id, directorate_id, uzlah_id, sub_location_id }
    onChange = () => {},
    entityTypeFilter = null, // اختياري لتصفية المستوى الرابع (مثل 'جمعية تعاونية' أو 'قرية')
    disabled = false,
    required = false,
    className = "",
    labels = {
        governorate: "المحافظة",
        directorate: "المديرية",
        uzlah: "العزلة / المركز",
        subLocation: "القرية / المحلة / الجمعية",
    }
}) {
    // اختيارات المستخدم
    const [selectedGov, setSelectedGov] = useState(value.governorate_id || "");
    const [selectedDir, setSelectedDir] = useState(value.directorate_id || "");
    const [selectedUzlah, setSelectedUzlah] = useState(value.uzlah_id || "");
    const [selectedSub, setSelectedSub] = useState(value.sub_location_id || "");

    // القوائم المحملة
    const [governorates, setGovernorates] = useState([]);
    const [directorates, setDirectorates] = useState([]);
    const [uzlahs, setUzlahs] = useState([]);
    const [subLocations, setSubLocations] = useState([]);

    // حالات التحميل
    const [loadingGovs, setLoadingGovs] = useState(false);
    const [loadingDirs, setLoadingDirs] = useState(false);
    const [loadingUzlahs, setLoadingUzlahs] = useState(false);
    const [loadingSubs, setLoadingSubs] = useState(false);

    // حالة النافذة المصغرة للإضافة السريعة (Inline Modal)
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [isSavingSub, setIsSavingSub] = useState(false);
    const [modalError, setModalError] = useState("");
    const [newSubForm, setNewSubForm] = useState({
        name_ar: "",
        entity_type: entityTypeFilter || "قرية",
        notes: "",
    });

    // 1. تحميل المحافظات عند بدء المكون
    useEffect(() => {
        setLoadingGovs(true);
        fetch('/api/locations/hierarchy')
            .then(res => res.json())
            .then(json => {
                if (json.data) {
                    setGovernorates(json.data);
                }
            })
            .catch(err => console.error("Error fetching governorates:", err))
            .finally(() => setLoadingGovs(false));
    }, []);

    // 2. تحميل المديريات عند اختيار محافظة
    useEffect(() => {
        if (!selectedGov) {
            setDirectorates([]);
            setSelectedDir("");
            setUzlahs([]);
            setSelectedUzlah("");
            setSubLocations([]);
            setSelectedSub("");
            return;
        }

        setLoadingDirs(true);
        fetch(`/geo/districts/${selectedGov}`)
            .then(res => res.json())
            .then(data => {
                setDirectorates(data || []);
            })
            .catch(err => console.error("Error fetching directorates:", err))
            .finally(() => setLoadingDirs(false));
    }, [selectedGov]);

    // 3. تحميل العزل عند اختيار مديرية
    useEffect(() => {
        if (!selectedDir) {
            setUzlahs([]);
            setSelectedUzlah("");
            setSubLocations([]);
            setSelectedSub("");
            return;
        }

        setLoadingUzlahs(true);
        fetch(`/geo/sub-districts/${selectedDir}`)
            .then(res => res.json())
            .then(data => {
                setUzlahs(data || []);
            })
            .catch(err => console.error("Error fetching uzlahs:", err))
            .finally(() => setLoadingUzlahs(false));
    }, [selectedDir]);

    // 4. تحميل المواقع الفرعية عند اختيار عزلة
    const fetchSubLocations = useCallback((uzlahId) => {
        if (!uzlahId) {
            setSubLocations([]);
            setSelectedSub("");
            return;
        }

        setLoadingSubs(true);
        const url = `/api/locations/sub-locations/by-uzlah/${uzlahId}` +
            (entityTypeFilter ? `?type=${encodeURIComponent(entityTypeFilter)}` : '');

        fetch(url)
            .then(res => res.json())
            .then(data => {
                setSubLocations(data || []);
            })
            .catch(err => console.error("Error fetching sub-locations:", err))
            .finally(() => setLoadingSubs(false));
    }, [entityTypeFilter]);

    useEffect(() => {
        fetchSubLocations(selectedUzlah);
    }, [selectedUzlah, fetchSubLocations]);

    // إرسال التغييرات للنموذج الأب
    const notifyChange = (gov, dir, uzl, sub) => {
        onChange({
            governorate_id: gov || null,
            directorate_id: dir || null,
            uzlah_id: uzl || null,
            sub_location_id: sub || null,
        });
    };

    const handleGovChange = (e) => {
        const val = e.target.value;
        setSelectedGov(val);
        setSelectedDir("");
        setSelectedUzlah("");
        setSelectedSub("");
        notifyChange(val, "", "", "");
    };

    const handleDirChange = (e) => {
        const val = e.target.value;
        setSelectedDir(val);
        setSelectedUzlah("");
        setSelectedSub("");
        notifyChange(selectedGov, val, "", "");
    };

    const handleUzlahChange = (e) => {
        const val = e.target.value;
        setSelectedUzlah(val);
        setSelectedSub("");
        notifyChange(selectedGov, selectedDir, val, "");
    };

    const handleSubChange = (e) => {
        const val = e.target.value;
        setSelectedSub(val);
        notifyChange(selectedGov, selectedDir, selectedUzlah, val);
    };

    // حفظ موقع فرعي جديد (Inline Modal Submission)
    const handleSaveNewSubLocation = async (e) => {
        e.preventDefault();
        if (!newSubForm.name_ar.trim()) {
            setModalError("يرجى إدخال اسم الموقع أو الجمعية");
            return;
        }

        setIsSavingSub(true);
        setModalError("");

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('/api/locations/sub-locations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                },
                body: JSON.stringify({
                    uzlah_id: selectedUzlah,
                    name_ar: newSubForm.name_ar,
                    entity_type: newSubForm.entity_type,
                    notes: newSubForm.notes,
                }),
            });

            const json = await res.json();

            if (!res.ok && res.status !== 200) {
                throw new Error(json.message || "تعذر حفظ الموقع الفرعي");
            }

            const createdItem = json.data;

            // تحديث القائمة فورياً بدون إعادة تحميل الصفحة
            setSubLocations(prev => {
                const exists = prev.some(item => item.id === createdItem.id);
                if (exists) return prev;
                return [...prev, createdItem].sort((a, b) => a.name_ar.localeCompare(b.name_ar, 'ar'));
            });

            // اختيار العنصر الجديد تلقائياً في القائمة
            setSelectedSub(createdItem.id);
            notifyChange(selectedGov, selectedDir, selectedUzlah, createdItem.id);

            // تصفير النموذج وإغلاق النافذة
            setNewSubForm({ name_ar: "", entity_type: entityTypeFilter || "قرية", notes: "" });
            setIsModalOpen(false);
        } catch (err) {
            setModalError(err.message);
        } finally {
            setIsSavingSub(false);
        }
    };

    return (
        <div className={`location-hierarchy-selector-container ${className}`}>
            <div className="row g-3">
                {/* 1. قائمة المحافظات */}
                <div className="col-12 col-md-3">
                    <label className="form-label fw-bold small text-muted">
                        {labels.governorate} {required && <span className="text-danger">*</span>}
                    </label>
                    <select
                        className="form-select"
                        value={selectedGov}
                        onChange={handleGovChange}
                        disabled={disabled || loadingGovs}
                        required={required}
                    >
                        <option value="">{loadingGovs ? "جاري التحميل..." : "-- اختر المحافظة --"}</option>
                        {governorates.map(gov => (
                            <option key={gov.id} value={gov.id}>
                                {gov.name_ar || gov.name}
                            </option>
                        ))}
                    </select>
                </div>

                {/* 2. قائمة المديريات */}
                <div className="col-12 col-md-3">
                    <label className="form-label fw-bold small text-muted">
                        {labels.directorate} {required && <span className="text-danger">*</span>}
                    </label>
                    <select
                        className="form-select"
                        value={selectedDir}
                        onChange={handleDirChange}
                        disabled={disabled || !selectedGov || loadingDirs}
                        required={required}
                    >
                        <option value="">
                            {!selectedGov ? "-- اختر المحافظة أولاً --" : (loadingDirs ? "جاري التحميل..." : "-- اختر المديرية --")}
                        </option>
                        {directorates.map(dir => (
                            <option key={dir.id} value={dir.id}>
                                {dir.name_ar || dir.name}
                            </option>
                        ))}
                    </select>
                </div>

                {/* 3. قائمة العزل */}
                <div className="col-12 col-md-3">
                    <label className="form-label fw-bold small text-muted">
                        {labels.uzlah} {required && <span className="text-danger">*</span>}
                    </label>
                    <select
                        className="form-select"
                        value={selectedUzlah}
                        onChange={handleUzlahChange}
                        disabled={disabled || !selectedDir || loadingUzlahs}
                        required={required}
                    >
                        <option value="">
                            {!selectedDir ? "-- اختر المديرية أولاً --" : (loadingUzlahs ? "جاري التحميل..." : "-- اختر العزلة --")}
                        </option>
                        {uzlahs.map(uzl => (
                            <option key={uzl.id} value={uzl.id}>
                                {uzl.name_ar || uzl.name}
                            </option>
                        ))}
                    </select>
                </div>

                {/* 4. قائمة القرى والجمعيات (المستوى الرابع الديناميكي مع زر الإضافة السريع) */}
                <div className="col-12 col-md-3">
                    <div className="d-flex justify-content-between align-items-center mb-1">
                        <label className="form-label fw-bold small text-muted mb-0">
                            {labels.subLocation} {required && <span className="text-danger">*</span>}
                        </label>
                        {selectedUzlah && !disabled && (
                            <button
                                type="button"
                                className="btn btn-sm btn-link text-primary p-0 text-decoration-none fw-bold"
                                onClick={() => setIsModalOpen(true)}
                                title="إضافة مسمى جديد تحت هذه العزلة"
                            >
                                <i className="fa-solid fa-plus me-1"></i> إضافة جديد
                            </button>
                        )}
                    </div>
                    <div className="input-group">
                        <select
                            className="form-select"
                            value={selectedSub}
                            onChange={handleSubChange}
                            disabled={disabled || !selectedUzlah || loadingSubs}
                            required={required}
                        >
                            <option value="">
                                {!selectedUzlah ? "-- اختر العزلة أولاً --" : (loadingSubs ? "جاري التحميل..." : "-- اختر أو أضف جديداً --")}
                            </option>
                            {subLocations.map(sub => (
                                <option key={sub.id} value={sub.id}>
                                    {sub.name_ar} {sub.entity_type ? `(${sub.entity_type})` : ''}
                                </option>
                            ))}
                        </select>
                        {selectedUzlah && !disabled && (
                            <button
                                type="button"
                                className="btn btn-outline-primary"
                                onClick={() => setIsModalOpen(true)}
                                title="إضافة قرية أو جمعية سريعة"
                            >
                                <i className="fa-solid fa-plus"></i>
                            </button>
                        )}
                    </div>
                </div>
            </div>

            {/* نافذة الإضافة المصغرة السريعة (Inline Modal) */}
            {isModalOpen && (
                <div className="modal show d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1060 }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content shadow border-0 rounded-3">
                            <div className="modal-header bg-light py-2">
                                <h6 className="modal-title fw-bold text-dark">
                                    <i className="fa-solid fa-plus-circle text-primary me-2"></i>
                                    إضافة (قرية / منطقة / جمعية) سريعة
                                </h6>
                                <button
                                    type="button"
                                    className="btn-close ms-auto"
                                    onClick={() => setIsModalOpen(false)}
                                    disabled={isSavingSub}
                                    aria-label="إغلاق"
                                ></button>
                            </div>
                            <form onSubmit={handleSaveNewSubLocation}>
                                <div className="modal-body p-3">
                                    {modalError && (
                                        <div className="alert alert-danger py-2 small mb-3">
                                            <i className="fa-solid fa-triangle-exclamation me-1"></i> {modalError}
                                        </div>
                                    )}

                                    <div className="mb-3">
                                        <label className="form-label small fw-bold text-muted">اسم المسمى الجديد (القرية أو الجمعية) <span className="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            placeholder="مثال: قرية البركة، أو جمعية الخير التعاونية"
                                            value={newSubForm.name_ar}
                                            onChange={(e) => setNewSubForm({ ...newSubForm, name_ar: e.target.value })}
                                            required
                                            autoFocus
                                        />
                                    </div>

                                    <div className="mb-3">
                                        <label className="form-label small fw-bold text-muted">نوع الكيان</label>
                                        <select
                                            className="form-select"
                                            value={newSubForm.entity_type}
                                            onChange={(e) => setNewSubForm({ ...newSubForm, entity_type: e.target.value })}
                                        >
                                            <option value="قرية">قرية</option>
                                            <option value="محلة">محلة</option>
                                            <option value="جمعية تعاونية">جمعية تعاونية</option>
                                            <option value="منطقة زراعية">منطقة زراعية</option>
                                            <option value="حي/حارة">حي / حارة</option>
                                            <option value="أخرى">أخرى</option>
                                        </select>
                                    </div>

                                    <div className="mb-2">
                                        <label className="form-label small fw-bold text-muted">ملاحظات إضافية (اختياري)</label>
                                        <textarea
                                            className="form-control"
                                            rows="2"
                                            placeholder="أي تفاصيل أو معلومات تخص الموقع..."
                                            value={newSubForm.notes}
                                            onChange={(e) => setNewSubForm({ ...newSubForm, notes: e.target.value })}
                                        ></textarea>
                                    </div>
                                </div>
                                <div className="modal-footer bg-light py-2">
                                    <button
                                        type="button"
                                        className="btn btn-secondary btn-sm"
                                        onClick={() => setIsModalOpen(false)}
                                        disabled={isSavingSub}
                                    >
                                        إلغاء
                                    </button>
                                    <button
                                        type="submit"
                                        className="btn btn-primary btn-sm"
                                        disabled={isSavingSub}
                                    >
                                        {isSavingSub ? (
                                            <>
                                                <span className="spinner-border spinner-border-sm me-1" role="status"></span>
                                                جاري الحفظ...
                                            </>
                                        ) : (
                                            <>
                                                <i className="fa-solid fa-check me-1"></i> حفظ وتحديد تلقائي
                                            </>
                                        )}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
