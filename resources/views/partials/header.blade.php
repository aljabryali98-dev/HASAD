<header class="app-header">
    <div class="d-flex align-items-center">
        <input class="form-control form-control-sm" placeholder="بحث سريع في حصاد..." type="search" style="width: 250px;">
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="dropdown">
            <a href="#" class="text-dark text-decoration-none dropdown-toggle fw-bold" data-bs-toggle="dropdown">
                <i class="fa-solid fa-circle-user fs-4 text-primary align-middle me-1"></i>
                {{ auth()->user()->name ?? 'المستخدم' }}
                <span class="badge bg-light text-primary border ms-1 small">
                    {{ auth()->user()->role === 'admin' ? 'مدير النظام' : 'مستخدم' }}
                </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-key me-2"></i>تعديل كلمة المرور</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" id="logout-form">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fa-solid fa-right-from-bracket me-2"></i>تسجيل الخروج
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
