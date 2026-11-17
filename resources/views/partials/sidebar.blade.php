<aside class="app-sidebar">
    <div class="sidebar-brand">
        <i class="fa-solid fa-seedling me-1"></i> نظام حصاد
        <div class="small text-muted fs-6 fw-normal">مركز الإحصاء والمتابعة والتحليل</div>
    </div>
    
    <ul class="side-menu">
        <li>
            <a href="{{ route('dashboard') }}" class="side-menu__item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-house"></i> الرئيسية
            </a>
        </li>

        <li class="side-menu__category">الكيانات المركزية والتشغيلية</li>

        <!-- القسم الأول: الجمعيات التعاونية -->
        <li>
            <a href="{{ route('associations.index') }}" class="side-menu__item {{ request()->routeIs('associations.*') ? 'active' : '' }}">
                <i class="fa-solid fa-building-columns text-primary"></i> الجمعيات التعاونية
            </a>
        </li>

        <!-- القسم الثاني: المجاميع الإنتاجية -->
        <li>
            <a href="{{ route('productive-groups.index') }}" class="side-menu__item {{ request()->routeIs('productive-groups.*') ? 'active' : '' }}">
                <i class="fa-solid fa-people-roof text-success"></i> المجاميع الإنتاجية
            </a>
        </li>

        <li class="side-menu__category">قاعدة بيانات عامة (المحاور الأربعة)</li>

        <!-- المحور (أ): الكوادر والفرسان والتدريب -->
        <li>
            <a href="{{ route('personnel.index') }}" class="side-menu__item {{ request()->routeIs('personnel.*') ? 'active' : '' }}">
                <i class="fa-solid fa-graduation-cap text-info"></i> الكوادر والفرسان والتدريب
            </a>
        </li>

        <!-- المحور (ب): المشاريع والمبادرات -->
        <li>
            <a class="side-menu__item d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menuProjects" role="button">
                <span><i class="fa-solid fa-diagram-project text-warning"></i> المشاريع والمبادرات</span>
                <i class="fa-solid fa-chevron-down small ms-0"></i>
            </a>
            <div class="collapse {{ request()->routeIs('general.projects.*', 'general.awareness.*', 'general.initiatives.*') ? 'show' : '' }}" id="menuProjects">
                <ul class="side-menu__sub">
                    <li>
                        <a href="{{ route('general.projects.index') }}" class="side-menu__sub-item {{ request()->routeIs('general.projects.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-circle-dot me-2 small"></i> مشاريع الجمعيات
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('general.awareness.index') }}" class="side-menu__sub-item {{ request()->routeIs('general.awareness.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-circle-dot me-2 small"></i> التوعية بالتطوع والجمعيات
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('general.initiatives.index') }}" class="side-menu__sub-item {{ request()->routeIs('general.initiatives.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-circle-dot me-2 small"></i> المبادرات المجتمعية
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- المحور (ج): الزراعة والإنتاج والمحاصيل -->
        <li>
            <a class="side-menu__item d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menuAgriculture" role="button">
                <span><i class="fa-solid fa-wheat-awn text-success"></i> الزراعة والمحاصيل</span>
                <i class="fa-solid fa-chevron-down small ms-0"></i>
            </a>
            <div class="collapse {{ request()->routeIs('general.crops.*', 'general.nurseries.*', 'general.farmers.*') ? 'show' : '' }}" id="menuAgriculture">
                <ul class="side-menu__sub">
                    <li>
                        <a href="{{ route('general.crops.index') }}" class="side-menu__sub-item {{ request()->routeIs('general.crops.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-circle-dot me-2 small"></i> سجل المحاصيل والإنتاج
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('general.nurseries.index') }}" class="side-menu__sub-item {{ request()->routeIs('general.nurseries.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-circle-dot me-2 small"></i> المشاتل الزراعية
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('general.farmers.index') }}" class="side-menu__sub-item {{ request()->routeIs('general.farmers.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-circle-dot me-2 small"></i> المزارعون والأراضي
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- المحور (د): التمويل والبنية التحتية -->
        <li>
            <a href="{{ route('general.loans.index') }}" class="side-menu__item {{ request()->routeIs('general.loans.*') ? 'active' : '' }}">
                <i class="fa-solid fa-hand-holding-dollar text-warning"></i> القروض التمويلية البيضاء
            </a>
        </li>

        <li class="side-menu__category">النطاق الجغرافي والتحليلات</li>

        <!-- مستكشف النطاق الجغرافي الشامل (Geo-Explorer) -->
        <li>
            <a href="{{ route('geo-explorer.index') }}" class="side-menu__item {{ request()->routeIs('geo-explorer.*') ? 'active' : '' }}">
                <i class="fa-solid fa-earth-asia text-primary"></i> المستكشف الجغرافي (34 قسماً)
            </a>
        </li>

        <!-- التقسيم الجغرافي الرسمي -->
        <li>
            <a href="{{ route('geography.index') }}" class="side-menu__item {{ request()->routeIs('geography.index') ? 'active' : '' }}">
                <i class="fa-solid fa-map-location-dot"></i> التقسيم الإداري الرسمي
            </a>
        </li>

        <!-- إدارة النطاق والجمعيات والقرى -->
        <li>
            <a href="{{ route('geography.manage') }}" class="side-menu__item {{ request()->routeIs('geography.manage') ? 'active' : '' }}">
                <i class="fa-solid fa-network-wired"></i> إدارة النطاق والقرى والجمعيات
            </a>
        </li>
    </ul>
</aside>
