@extends('layouts.app')

@section('content')

    <!-- Hero / Form Area -->
    <div class="hero-section text-center text-white py-5 position-relative">
        <div class="container position-relative z-1 mb-5 pt-3">
            <h1 class="fw-bold fs-2 mb-3 title-arabic">جمع الدورات التعليمية من يوتيوب</h1>
            <p class="text-white-50 fs-6 fw-light mb-0">أدخل التصنيفات واضغط ابدأ - النظام سيجمع الدورات تلقائياً باستخدام
                الذكاء الاصطناعي</p>
        </div>
    </div>

    <div class="container mt-n5 position-relative z-2 form-container-wrapper">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden p-4 mx-auto form-card bg-white">
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('scrape') }}" method="POST" class="row gx-5">
                    @csrf

                    <div class="col-md-9 text-start">
                        <label class="form-label text-secondary small fw-bold mb-3 d-block">أدخل التصنيفات (كل تصنيف في سطر
                            جديد)</label>
                        <textarea name="categories" rows="6"
                            class="form-control bg-light border-0 shadow-none prompt-textarea p-3"
                            placeholder="التسويق&#10;البرمجة&#10;الجرافيكس&#10;الهندسة&#10;إدارة الأعمال"></textarea>
                    </div>

                    <div
                        class="col-md-3 d-flex flex-column align-items-center justify-content-center border-start form-btn-col pt-4 pt-md-0">
                        <button type="submit"
                            class="btn btn-danger btn-lg w-100 mb-3 fw-bold bg-youtube hover-slide py-3 rounded-3 shadow-none">
                            ابدأ الجمع
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="ms-1" stroke="currentColor"
                                stroke-width="2">
                                <path d="M5 12h14m-7-7l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <button type="button"
                            class="btn btn-outline-secondary w-100 fw-bold py-2 rounded-3 shadow-none text-muted border-light border-2 bg-light bg-opacity-50 hover-bg-light">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" class="ms-1">
                                <rect width="24" height="24" rx="4" />
                            </svg>
                            إيقاف
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Filter & Stats -->
    <div class="container my-5 pt-4">
        <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
            <div class="text-start">
                <h4 class="fw-bold fs-5 mb-2 text-dark">الدورات المكتشفة</h4>
                <div class="text-muted small">تم العثور على {{ $playlists->total() }} دورة في {{ $categories->count() }}
                    تصنيفات</div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('home') }}"
                    class="btn rounded-pill px-4 fw-bold shadow-sm {{ !$categoryId ? 'btn-danger bg-youtube text-white' : 'btn-white text-secondary' }}">
                    الكل ({{ $totalPlaylists }})
                </a>
                @foreach($categories as $category)
                    <a href="{{ route('home', ['category_id' => $category->id]) }}"
                        class="btn rounded-pill px-4 fw-bold shadow-sm {{ $categoryId == $category->id ? 'btn-danger bg-youtube text-white' : 'btn-white text-secondary' }}">
                        {{ $category->name }} ({{ $category->courses_count }})
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Grid -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 custom-grid">
            @forelse($playlists as $playlist)
                <div class="col">
                    <a href="{{ config('youtube.endpoints.playlist_url') }}{{ $playlist->playlist_id }}" target="_blank" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden course-card pb-1">
                            <div class="position-relative thumbnail-wrapper bg-light">
                                @if($playlist->thumbnail)
                                    <img src="{{ $playlist->thumbnail }}" class="card-img-top w-100 h-100 object-fit-cover"
                                        alt="{{ $playlist->title }}">
                                @else
                                    <div class="bg-secondary w-100 h-100 d-flex align-items-center justify-content-center">
                                        <span class="text-white-50">لا توجد صورة</span>
                                    </div>
                                @endif
                                <span class="position-absolute top-0 start-0 bg-danger text-white rounded-pill px-2 py-1 mx-2 my-2 small fw-bold lesson-badge shadow-sm">{{ $playlist->lessons_count }} دروس</span>

                                @php
                                    $hours = floor($playlist->duration_seconds / 3600);
                                    $minutes = floor(($playlist->duration_seconds % 3600) / 60);
                                    $durationText = '';
                                    if ($hours > 0) $durationText .= $hours . ' ساعة ';
                                    if ($minutes > 0 || $hours == 0) $durationText .= $minutes . ' دقيقة';
                                @endphp
                                <span class="position-absolute bottom-0 start-0 bg-dark text-white rounded-pill px-2 py-1 mx-2 my-2 small time-badge shadow-sm">{{ trim($durationText) }}</span>
                            </div>
                            <div class="card-body p-4 d-flex flex-column text-end mt-1">
                                <h6 class="card-title fw-bold text-truncate-2 mb-3 lh-base text-dark fs-6">{{ $playlist->title }}
                                </h6>
                                <div class="d-flex align-items-center gap-2 mb-4 mt-auto justify-content-start text-muted">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" class="opacity-75">
                                        <path d="M20 21V19A4 4 0 0016 15H8A4 4 0 004 19V21M16 7A4 4 0 118 7 4 4 0 0116 7Z"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span class="small text-truncate fw-medium">{{ $playlist->channel_name }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top border-light mt-auto">
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2 fw-bold small">
                                        {{ $playlist->courses->first()?->categories->first()?->name ?? 'غير محدد' }}
                                    </span>
                                    <span class="text-muted small opacity-50 fw-medium">{{ number_format($playlist->view_count) }} مشاهدة</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12 py-5 text-center text-muted">
                    لا توجد دورات حتى الآن. أضف التصنيفات واضغط على "ابدأ الجمع"!
                </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-center mt-5">
            {{ $playlists->links('pagination::bootstrap-5') }}
        </div>
    </div>

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let initialCheckDelay = setTimeout(() => {
                    let checkInterval = setInterval(() => {
                        fetch('{{ route("scrape.status") }}')
                            .then(response => response.json())
                            .then(data => {
                                if (data.jobs_count === 0) {
                                    clearInterval(checkInterval);
                                    alert('🎉 تم الانتهاء من جلب جميع الدورات والتحليلات بنجاح!');
                                    window.location.reload();
                                }
                            })
                            .catch(error => console.error('Error checking status:', error));
                    }, 4000); // Poll every 4 seconds
                }, 2000); // Wait 2s before first poll to ensure jobs DB is locked
            });
        </script>
    @endif
@endsection