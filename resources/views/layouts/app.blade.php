<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YouTube Course Scraper</title>

    <!-- Bootstrap RTL CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <!-- Google Fonts: Tajawal -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>

<body class="bg-light">

    <!-- Header -->
    <nav class="navbar navbar-expand-lg border-bottom shadow-sm top-nav bg-white">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2 brand-logo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="#d9534f" xmlns="http://www.w3.org/2000/svg">
                    <rect width="24" height="24" rx="6" />
                    <path d="M15 12L10 16V8L15 12Z" fill="white" />
                </svg>
                <span class="fs-5 fw-bold text-danger ps-2 pe-2 border-start border-end">YouTube Course Scraper</span>
                <span class="text-secondary opacity-75 small">أداة جمع الدورات التعليمية</span>

                <!-- Play Icon Placeholder SVG -->

            </div>
        </div>
    </nav>

    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>