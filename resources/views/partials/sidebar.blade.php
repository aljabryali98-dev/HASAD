<aside class="app-sidebar">
    <!-- الشعار والهوية المؤسسية السيادية -->
    <div class="sidebar-brand-wrapper">
        <a href="{{ route('dashboard') }}" class="sidebar-brand text-decoration-none">
            <div class="sidebar-brand-icon">
                <i class="fa-solid fa-seedling" data-lucide="sprout"></i>
            </div>
            <div class="sidebar-brand-text">
                <div class="d-flex align-items-center gap-1">
                    <h5 class="mb-0">نظام حصاد</h5>
                    <span class="badge bg-success-subtle text-success border px-1 py-0 font-monospace" style="font-size: 0.65rem;">ERP</span>
                </div>
                <span>الجمهورية اليمنية - اللجنة الزراعية العليا</span>
            </div>
        </a>
    </div>
    
    <!-- قائمة الروابط المصنفة -->
    <div class="sidebar-menu-wrapper">
        <ul class="side-menu">
            <li>
                <a href="{{ route('dashboard') }}" class="side-menu__item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-house" data-lucide="layout-dashboard"></i>
                    <span>لوحة التحكم الرئيسية</span>
                </a>
            </li>

            <li class="side-menu__category">الكيانات المركزية والتشغيلية</li>

            <!-- القسم الأول: الجمعيات التعاونية -->
            <li>
                <a href="{{ route('associations.index') }}" class="side-menu__item {{ request()->routeIs('associations.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-building-columns" data-lucide="landmark"></i>
                    <span>الجمعيات التعاونية</span>
                </a>
            </li>

            <!-- القسم الثاني: المجاميع الإنتاجية -->
            <li>
                <a href="{{ route('productive-groups.index') }}" class="side-menu__item {{ request()->routeIs('productive-groups.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-people-roof" data-lucide="users"></i>
                    <span>المجاميع الإنتاجية</span>
                </a>
            </li>

            <li class="side-menu__category">قاعدة البيانات العامة (المحاور)</li>

            <!-- المحور (أ): الكوادر والفرسان والتدريب -->
            <li>
                <a href="{{ route('personnel.index') }}" class="side-menu__item {{ request()->routeIs('personnel.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-graduate" data-lucide="graduation-cap"></i>
                    <span>الكوادر والفرسان والتدريب</span>
                </a>
            </li>

            <!-- المحور (ب): المشاريع والمبادرات -->
            <li>
                <a class="side-menu__item d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menuProjects" role="button" aria-expanded="{{ request()->routeIs('general.projects.*', 'general.awareness.*', 'general.initiatives.*') ? 'true' : 'false' }}">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-diagram-project" data-lucide="folder-kanban"></i>
                        <span>المشاريع والمبادرات</span>
                    </div>
                    <i class="fa-solid fa-chevron-down small text-muted" style="font-size: 0.75rem;"></i>
                </a>
                <div class="collapse {{ request()->routeIs('general.projects.*', 'general.awareness.*', 'general.initiatives.*') ? 'show' : '' }}" id="menuProjects">
                    <ul class="side-menu__sub">
                        <li>
                            <a href="{{ route('general.projects.index') }}" class="side-menu__sub-item {{ request()->routeIs('general.projects.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-circle" data-lucide="briefcase"></i>
                                <span>مشاريع الجمعيات</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('general.awareness.index') }}" class="side-menu__sub-item {{ request()->routeIs('general.awareness.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-circle" data-lucide="megaphone"></i>
                                <span>التوعية بالتطوع والجمعيات</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('general.initiatives.index') }}" class="side-menu__sub-item {{ request()->routeIs('general.initiatives.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-circle" data-lucide="handshake"></i>
                                <span>المبادرات المجتمعية</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- المحور (ج): الزراعة والإنتاج والمحاصيل -->
            <li>
                <a class="side-menu__item d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menuAgriculture" role="button" aria-expanded="{{ request()->routeIs('general.crops.*', 'general.nurseries.*', 'general.farmers.*') ? 'true' : 'false' }}">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-wheat-awn" data-lucide="wheat"></i>
                        <span>الزراعة والمحاصيل</span>
                    </div>
                    <i class="fa-solid fa-chevron-down small text-muted" style="font-size: 0.75rem;"></i>
                </a>
                <div class="collapse {{ request()->routeIs('general.crops.*', 'general.nurseries.*', 'general.farmers.*') ? 'show' : '' }}" id="menuAgriculture">
                    <ul class="side-menu__sub">
                        <li>
                            <a href="{{ route('general.crops.index') }}" class="side-menu__sub-item {{ request()->routeIs('general.crops.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-circle" data-lucide="sprout"></i>
                                <span>سجل المحاصيل والإنتاج</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('general.nurseries.index') }}" class="side-menu__sub-item {{ request()->routeIs('general.nurseries.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-circle" data-lucide="trees"></i>
                                <span>المشاتل الزراعية</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('general.farmers.index') }}" class="side-menu__sub-item {{ request()->routeIs('general.farmers.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-circle" data-lucide="tractor"></i>
                                <span>المزارعون والأراضي</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- المحور (د): التمويل والتمكين -->
            <li>
                <a href="{{ route('general.loans.index') }}" class="side-menu__item {{ request()->routeIs('general.loans.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-hand-holding-dollar" data-lucide="hand-coins"></i>
                    <span>القروض التمويلية البيضاء</span>
                </a>
            </li>

            <li class="side-menu__category">النطاق الجغرافي والتحليلات</li>

            <!-- مستكشف النطاق الجغرافي الشامل (Geo-Explorer) -->
            <li>
                <a href="{{ route('geo-explorer.index') }}" class="side-menu__item {{ request()->routeIs('geo-explorer.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-earth-asia" data-lucide="globe"></i>
                    <span>المستكشف الجغرافي (34 قسماً)</span>
                </a>
            </li>

            <!-- التقسيم الجغرافي الرسمي -->
            <li>
                <a href="{{ route('geography.index') }}" class="side-menu__item {{ request()->routeIs('geography.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-map-location-dot" data-lucide="map-pin"></i>
                    <span>التقسيم الإداري الرسمي</span>
                </a>
            </li>

            <!-- إدارة النطاق والجمعيات والقرى -->
            <li>
                <a href="{{ route('geography.manage') }}" class="side-menu__item {{ request()->routeIs('geography.manage') ? 'active' : '' }}">
                    <i class="fa-solid fa-network-wired" data-lucide="network"></i>
                    <span>إدارة النطاق والقرى والجمعيات</span>
                </a>
            </li>

            <li class="side-menu__category">أدوات النظام والمعالجة الذكية</li>

            <!-- الاستيراد الذكي الشامل من ملفات الإكسل -->
            <li>
                <a href="{{ route('import.smart') }}" class="side-menu__item {{ request()->routeIs('import.smart*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-excel text-success" data-lucide="file-spreadsheet"></i>
                    <span>الاستيراد الذكي الشامل (Excel)</span>
                    <span class="badge bg-emerald-500/10 text-success border border-emerald-500/20 ms-auto font-monospace" style="font-size: 0.65rem;">شامل</span>
                </a>
            </li>

            <!-- تثبيت تطبيق حصاد (PWA Universal Install) -->
            <li>
                <a href="javascript:void(0)" onclick="handlePwaInstallClick()" class="side-menu__item">
                    <i class="fa-solid fa-mobile-screen-button text-success" data-lucide="smartphone"></i>
                    <span>تثبيت التطبيق (PWA)</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle ms-auto" style="font-size: 0.65rem;">تثبيت</span>
                </a>
            </li>
        </ul>

        <!-- بطاقة تثبيت التطبيق السريع -->
        <div class="px-3 py-2 mt-2">
            <div class="p-2 rounded-3 border text-center" style="background: rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.2) !important;">
                <div class="d-flex align-items-center justify-content-center gap-1 text-success fw-bold small mb-1" style="font-size: 0.8rem;">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                    <span>تثبيت على أجهزتك</span>
                </div>
                <div class="text-muted small mb-2" style="font-size: 0.7rem; line-height: 1.3;">
                    لابتوب • أندرويد • آيفون
                </div>
                <button type="button" class="btn btn-sm btn-success w-100 rounded-pill py-1 small fw-semibold shadow-xs" onclick="handlePwaInstallClick()" style="font-size: 0.75rem;">
                    <i class="fa-solid fa-download me-1"></i> تثبيت التطبيق
                </button>
            </div>
        </div>
    </div>
</aside>
