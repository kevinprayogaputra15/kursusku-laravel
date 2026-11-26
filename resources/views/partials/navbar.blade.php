<nav class="navbar navbar-expand-lg bg-dark navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            KursusKu
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNavbar"
                aria-controls="mainNavbar"
                aria-expanded="false"
                aria-label="Buka menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a @class(['nav-link', 'active' => request()->routeIs('home')])
                       href="{{ route('home') }}">Beranda</a>
                </li>
                <li class="nav-item">
                    <a @class(['nav-link', 'active' => request()->routeIs('courses.*')])
                       href="{{ route('courses.index') }}">Daftar Kursus</a>
                </li>
                <li class="nav-item">
                    <a @class(['nav-link', 'active' => request()->routeIs('enrollments.create', 'enrollments.store')])
                       href="{{ route('enrollments.create') }}">Pendaftaran</a>
                </li>
                <li class="nav-item">
                    <a @class(['nav-link', 'active' => request()->routeIs('enrollments.index')])
                       href="{{ route('enrollments.index') }}">Laporan Enrollment</a>
                </li>

                @auth
                    <li class="nav-item">
                        <a @class(['nav-link', 'active' => request()->routeIs('dashboard')])
                           href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                Keluar ({{ \Illuminate\Support\Str::limit(auth()->user()->name, 14) }})
                            </button>
                        </form>
                    </li>
                @else
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a href="{{ route('login') }}" class="btn btn-sm btn-primary">Masuk</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
