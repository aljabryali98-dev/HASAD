<aside class="app-sidebar">
    <!-- الشعار والهوية المؤسسية -->
    <div class="sidebar-brand-wrapper">
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i class="fa-solid fa-seedling"></i>
            </div>
            <div class="sidebar-brand-text">
                <h5>نظام حصاد</h5>
                <span>المنظومة الوطنية للرصد والتقييم</span>
            </div>
        </a>
    </div>
    
    <!-- قائمة الروابط المصنفة -->
    <div class="sidebar-menu-wrapper">
        <ul class="side-menu">
            <li>
                <a href="{{ route('dashboard') }}" class="side-menu__item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-house"></i>
                    <span>لوحة التحكم الرئيسية</span>
                </a>
            </li>

            <li class="side-menu__category">الكيانات المركزية والتشغيلية</li>

            <!-- القسم الأول: الجمعيات التعاونية -->
            <li>
                <a href="{{ route('associations.index') }}" class="side-menu__item {{ request()->routeIs('associations.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-building-columns"></i>
                    <span>الجمعيات التعاونية</span>
                </a>
            </li>

            <!-- القسم الثاني: المجاميع الإنتاجية -->
            <li>
                <a href="{{ route('productive-groups.index') }}" class="side-menu__item {{ request()->routeIs('productive-groups.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-people-roof"></i>
                    <span>المجاميع الإنتاجية</span>
                </a>
            </li>

            <li class="side-menu__category">قاعدة البيانات العامة (المحاور)</li>

            <!-- المحور (أ): الكوادر والفرسان والتدريب -->
            <li>
                <a href="{{ route('personnel.index') }}" class="side-menu__item {{ request()->routeIs('personnel.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-graduate"></i>
                    <span>الكوادر والفرسان والتدريب</span>
                </a>
            </li>

            <!-- المحور (ب): المشاريع والمبادرات -->
            <li>
                <a class="side-menu__item d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menuProjects" role="button" aria-expanded="{{ request()->routeIs('general.projects.*', 'general.awareness.*', 'general.initiatives.*') ? 'true' : 'false' }}">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-diagram-project"></i>
                        <span>المشاريع والمبادرات</span>
                    </div>
                    <i class="fa-solid fa-chevron-down small text-muted" style="font-size: 0.75rem;"></i>
                </a>
                <div class="collapse {{ request()->routeIs('general.projects.*', 'general.awareness.*', 'general.initiatives.*') ? 'show' : '' }}" id="menuProjects">
                    <ul class="side-menu__sub">
                        <li>
                            <a href="{{ route('general.projects.index') }}" class="side-menu__sub-item {{ request()->routeIs('general.projects.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-circle"></i>
                                <span>مشاريع الجمعيات</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('general.awareness.index') }}" class="side-menu__sub-item {{ request()->routeIs('general.awareness.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-circle"></i>
                                <span>التوعية بالتطوع والجمعيات</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('general.initiatives.index') }}" class="side-menu__sub-item {{ request()->routeIs('general.initiatives.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-circle"></i>
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
                        <i class="fa-solid fa-wheat-awn"></i>
                        <span>الزراعة والمحاصيل</span>
                    </div>
                    <i class="fa-solid fa-chevron-down small text-muted" style="font-size: 0.75rem;"></i>
                </a>
                <div class="collapse {{ request()->routeIs('general.crops.*', 'general.nurseries.*', 'general.farmers.*') ? 'show' : '' }}" id="menuAgriculture">
                    <ul class="side-menu__sub">
                        <li>
                            <a href="{{ route('general.crops.index') }}" class="side-menu__sub-item {{ request()->routeIs('general.crops.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-circle"></i>
                                <span>سجل المحاصيل والإنتاج</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('general.nurseries.index') }}" class="side-menu__sub-item {{ request()->routeIs('general.nurseries.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-circle"></i>
                                <span>المشاتل الزراعية</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('general.farmers.index') }}" class="side-menu__sub-item {{ request()->routeIs('general.farmers.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-circle"></i>
                                <span>المزارعون والأراضي</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- المحور (د): التمويل والتمكين -->
            <li>
                <a href="{{ route('general.loans.index') }}" class="side-menu__item {{ request()->routeIs('general.loans.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    <span>القروض التمويلية البيضاء</span>
                </a>
            </li>

            <li class="side-menu__category">النطاق الجغرافي والتحليلات</li>

            <!-- مستكشف النطاق الجغرافي الشامل (Geo-Explorer) -->
            <li>
                <a href="{{ route('geo-explorer.index') }}" class="side-menu__item {{ request()->routeIs('geo-explorer.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-earth-asia"></i>
                    <span>المستكشف الجغرافي (34 قسماً)</span>
                </a>
            </li>

            <!-- التقسيم الجغرافي الرسمي -->
            <li>
                <a href="{{ route('geography.index') }}" class="side-menu__item {{ request()->routeIs('geography.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <span>التقسيم الإداري الرسمي</span>
                </a>
            </li>

            <!-- إدارة النطاق والجمعيات والقرى -->
            <li>
                <a href="{{ route('geography.manage') }}" class="side-menu__item {{ request()->routeIs('geography.manage') ? 'active' : '' }}">
                    <i class="fa-solid fa-network-wired"></i>
                    <span>إدارة النطاق والقرى والجمعيات</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
