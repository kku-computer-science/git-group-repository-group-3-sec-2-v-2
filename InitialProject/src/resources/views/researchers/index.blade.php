@extends('layouts.layout')

@section('content')
<style>
    .container-fluid {
        padding-right: 0 !important;
        padding-left: 0 !important;
        max-width: 100vw;
        /* ป้องกันการเกินขอบจอ */
        overflow-x: hidden;
    }

    .content-wrapper {
        padding: 0 !important;
        margin: 0 !important;
        width: 100vw !important;
        /* กว้างเต็มหน้าจอ */
        max-width: 100% !important;
        /* ป้องกันขนาดถูกบีบ */
    }

    .img-cover {
        object-fit: cover;
        object-position: center;
    }

    .card-hover:hover .card {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .transition-all {
        transition: all 0.3s ease;
    }

    .search-form {
        max-width: 600px;
        margin: 0 auto;
    }

    /* ปรับช่องว่างด้านซ้ายและขวาของ Accordion */
    .custom-accordion {
        max-width: 1400px !important;
        /* ปรับให้เท่ากับ .category-container */
        margin: 0 auto !important;
        /* จัดกึ่งกลาง */
    }

    .custom-accordion .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        color: var(--bs-primary);
    }

    .custom-accordion .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0, 0, 0, 0.125);
    }

    .expertise-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .badge {
        font-weight: 500;
        padding: 0.5em 0.8em;
    }

    .readmore-content.d-none {
        display: none;
    }

    .readmore-toggle {
        cursor: pointer;
        order: 1;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* ปรับแต่ง Category Container */
    .category-container {
        margin: 0 auto;
        padding: 10px 20px;
        max-width: 1400px;
        display: flex;
        align-items: center;
        gap: 10px;
        overflow: hidden;
        position: relative;
        background-color: #fff;
    }

    .category-wrapper {
        display: flex;
        overflow-x: auto;
        scroll-behavior: smooth;
        gap: 15px;
        padding: 10px;
        white-space: nowrap;
        scrollbar-width: none;
        /* ซ่อน Scrollbar */
    }

    .category-wrapper::-webkit-scrollbar {
        display: none;
    }

    .category-item {
        padding: 10px 20px;
        background-color: #E9F1FA;
        color: #2C6FA8;
        font-weight: bold;
        border-radius: 25px;
        flex-shrink: 0;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        /* เพิ่มเงา */
    }

    .category-item.active {
        background-color: #2C6FA8;
        color: white;
    }

    .arrow {
        font-size: 24px;
        background: none;
        border: none;
        cursor: pointer;
        transition: 0.3s;
    }

    .arrow:hover {
        color: #2C6FA8;
    }

    .input-group {
        position: relative;
        width: 100%;
    }

    .search-input {
        padding-right: 50px !important;
        /* เว้นที่ให้ปุ่มอยู่ */
        position: relative;
        z-index: 1;
        /* ทำให้ input อยู่ต่ำกว่าไอคอน */
        background-color: white;
        /* ป้องกันสีทับ */
        border-radius: 25px !important;
        /* เพิ่ม !important */
        font-size: 16px !important;
    }

    /* ปุ่ม search ต้องอยู่บนสุดเสมอ */
    .search-button {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        cursor: pointer;
        z-index: 3;
        /* ป้องกันปุ่มถูกบัง */
    }

    /* ป้องกัน input ไปบังปุ่ม */
    .search-input:focus {
        z-index: 1;
    }

    /* ป้องกันไอคอนถูกบัง */
    .search-icon {
        color: rgb(0, 0, 0);
        pointer-events: none;
        /* ป้องกันการกดผิด */
    }

    /* ป้องกันปุ่ม search ถูกซ่อน */
    .search-button:focus,
    .search-button:active {
        z-index: 3;
    }

    /* ปรับแต่งแถบ Accordion */
    .custom-accordion-btn {
        margin-top: 2%;
        background-color: #2C6FA8 !important;
        /* สีฟ้าตาม UI */
        color: white !important;
        border-radius: 50px !important;
        /* มุมโค้งมน */
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        border: none;
        box-shadow: none;
        transition: all 0.3s ease;
        padding: 10px 20px !important;
        /* เพิ่ม Padding ให้สมดุล */
        height: 50px !important;
        /* กำหนดความสูงของกล่องให้สมมาตร */
    }

    /* ปรับแต่งตัวหนังสือให้ชัด */
    .custom-accordion-btn span {
        font-size: 1.2rem;
    }

    /* ปรับแต่งตัวเลขให้ดูชัด */
    .custom-accordion-btn .badge {
        font-size: 1rem;
        padding: 5px 10px;
        border-radius: 20px;
    }

    /* ปรับแต่งไอคอนลูกศร */
    .accordion-arrow {
        background-color: rgba(255, 255, 255, 0.2);
        padding: 8px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .accordion-arrow ion-icon {
        color: white;
        font-size: 1rem !important;
        /* ลดขนาดไอคอน */
    }

    /* เปลี่ยนสีไอคอนเมื่อ hover */
    .custom-accordion-btn:hover .accordion-arrow {
        background-color: rgba(255, 255, 255, 0.5);
    }

    /* ซ่อนลูกศรขวาสุดของ Bootstrap Accordion */
    .accordion-button::after {
        display: none !important;
    }

    /* Grid Layout: แสดงเป็น 2 คอลัมน์ */
    .researcher-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        /* ให้การ์ดขยายตัว */
        gap: 30px;
        /* เพิ่มช่องว่างระหว่างการ์ด */
        justify-content: center;
        padding: 20px;
        width: 100%;
        /* กว้างเต็มหน้าจอ */
    }

    /* การ์ดของแต่ละ Researcher */
    .researcher-card {
        display: flex;
        align-items: stretch;
        /* ทำให้การ์ดมีขนาดเท่ากัน */
        background-color: white;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        gap: 20px;
        transition: all 0.3s ease-in-out;
        min-height: 220px;
        /* กำหนดความสูงขั้นต่ำให้เท่ากัน */
    }

    /* Hover Effect */
    .researcher-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    /* ปรับแต่งรูปภาพ */
    .researcher-image {
        width: 150px;
        /* หรือปรับให้เหมาะสม */
        max-height: 200px;
        object-fit: cover;
        border-radius: 10px;
    }

    /* ข้อมูลผู้วิจัย */
    .researcher-info {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex-grow: 1;
    }

    /* ชื่อและตำแหน่ง */
    .researcher-name {
        font-size: 1.2rem;
        font-weight: bold;
        color: #2C6FA8;
    }

    .researcher-position {
        font-size: 1rem;
        color: gray;
    }

    /* Email */
    .researcher-email {
        color: #007bff;
        font-size: 0.95rem;
        text-decoration: none;
        display: flex;
        align-items: center;
        margin-top: 5px;
    }

    .researcher-email ion-icon {
        margin-right: 5px;
    }

    /* ป้าย Research Interests */
    .research-interests {
        margin-top: 10px;
    }

    .research-interests span {
        background-color: #E9F1FA;
        color: #2C6FA8;
        font-size: 0.9rem;
        padding: 5px 10px;
        border-radius: 10px;
        margin-right: 5px;
        display: inline-block;
    }

    /* ปรับแต่ง Readmore */
    .readmore-toggle {
        cursor: pointer;
        font-weight: bold;
        color: #007bff;
    }
    
    /* ข้อความแจ้งเตือนไม่พบผลลัพธ์ */
    .no-results-message {
        text-align: center;
        padding: 40px 20px;
        background-color: #f8f9fa;
        border-radius: 10px;
        margin: 30px auto;
        max-width: 800px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }
    
    .no-results-message h3 {
        color: #2C6FA8;
        margin-bottom: 15px;
    }
    
    .no-results-message p {
        color: #6c757d;
        font-size: 1.1rem;
        margin-bottom: 20px;
    }
    
    .no-results-message .btn {
        padding: 10px 25px;
        font-weight: 500;
    }
    
    /* ข้อความแจ้งเตือนไม่มีข้อมูลในหมวดหมู่ */
    .no-data-message {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 30px;
        margin: 20px 0;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }
    
    .no-data-message h4 {
        font-size: 1.3rem;
        margin-bottom: 10px;
        color: #2C6FA8;
    }
    
    .no-data-message p {
        font-size: 1rem;
        color: #6c757d;
    }

    /* Responsive สำหรับมือถือ */
    @media (max-width: 992px) {
        .researcher-container {
            grid-template-columns: repeat(1, 1fr);
            /* แสดงเป็น 1 คอลัมน์เมื่อจอเล็กลง */
        }

        .researcher-card {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .researcher-image {
            width: 120px;
            height: 120px;
        }
    }
</style>

<div class="container-fluid" style="padding-right: 0px;">
    <!-- Header Section -->
    <div class="row mb-5" style="height: 250px; background-color: #1075BB; display: flex; flex-direction: column; justify-content: center; align-items: center; margin-bottom: 20px; box-sizing: border-box;">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 fw-bold mb-4" style="color: white;">OUR RESEARCHERS</h1>
            <div class="row my-4">
                <div class="col-lg-8 mx-auto">
                    <div class="search-box bg-white p-3 p-md-4 rounded shadow-sm">
                        <form action="{{ route('researchers.index') }}" method="GET" class="d-flex align-items-center">
                            <div class="flex-grow-1 me-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control border-start-0" name="textsearch" 
                                        value="{{ $search }}" placeholder="{{ __('Search researchers by name, expertise, or program...') }}" 
                                        aria-label="Search researchers">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary px-4">{{ __('Search') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($noResults) && $noResults)
        <!-- แสดงข้อความเมื่อไม่พบผลลัพธ์ -->
        <div class="no-results-message">
            <h3><ion-icon name="search-outline" class="me-2"></ion-icon> No Results Found</h3>
            <p>Sorry, we couldn't find any researchers matching "{{ $search }}".</p>
            <a href="{{ route('researchers.index') }}" class="btn btn-primary">
                <ion-icon name="refresh-outline" class="me-1"></ion-icon> Clear Search
            </a>
        </div>
    @else
        <!-- Category Slider Section -->
        <div class="category-container">
            <button class="arrow" onclick="scrollCategoryLeft()">&#10094;</button>
            <div class="category-wrapper" id="categoryWrapper">
                @foreach($roleUsers as $roleId => $roleData)
                    <div class="category-item" onclick="scrollToCategory('category-{{ $roleId }}')" id="category-{{ $roleId }}">
                        {{ strtoupper($roleData['role_name']) }}
                    </div>
                @endforeach
            </div>
            <button class="arrow" onclick="scrollCategoryRight()">&#10095;</button>
        </div>

        <!-- Accordion Section -->
        <div class="accordion custom-accordion" id="roleAccordion">
            @foreach($roleUsers as $roleId => $roleData)
                <div class="accordion-item mb-4">
                    <h2 class="accordion-header" id="heading{{ $roleId }}">
                        <button class="accordion-button custom-accordion-btn {{ !in_array($roleId, $expandedRoleIds) ? 'collapsed' : '' }}"
                            type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $roleId }}"
                            aria-expanded="{{ in_array($roleId, $expandedRoleIds) ? 'true' : 'false' }}"
                            aria-controls="collapse{{ $roleId }}">
                            {{ strtoupper($roleData['role_name']) }}
                            <span class="badge bg-light text-primary ms-2">{{ $roleData['users']->count() }}</span>
                        </button>
                    </h2>
                    <div id="collapse{{ $roleId }}"
                        class="accordion-collapse collapse {{ in_array($roleId, $expandedRoleIds) ? 'show' : '' }}"
                        aria-labelledby="heading{{ $roleId }}">
                        <div class="accordion-body">
                            @if($roleData['users']->isNotEmpty())
                                <div class="researcher-container">
                                    @foreach($roleData['users'] as $user)
                                        <div class="col-12 mb-4">
                                            <div class="researcher-card">
                                                <div class="d-flex align-items-start">
                                                    @if(isset($user->picture))
                                                        <img class="researcher-image me-3"
                                                            src="{{ $user->picture }}"
                                                            alt="{{ $user->fname_en ?? $user->author_fname }}'s photo">
                                                    @else
                                                        <img class="researcher-image me-3"
                                                            src="{{ asset('img/default-profile.png') }}"
                                                            alt="{{ $user->fname_en ?? $user->author_fname }}'s photo">
                                                    @endif

                                                    <div class="researcher-info flex-grow-1">
                                                        <span class="researcher-name d-block text-primary fw-bold">
                                                            @if(isset($user->fname_en))
                                                                {{ $user->{'fname_'.app()->getLocale()} }} {{ $user->{'lname_'.app()->getLocale()} }}
                                                            @else
                                                                {{ $user->author_fname }} {{ $user->author_lname }}
                                                            @endif
                                                        </span>
                                                        
                                                        @if(isset($user->position_en))
                                                            <span class="researcher-position text-muted">{{ $user->position_en }}</span>
                                                        @endif
                                                        
                                                        @if($roleId === 'external' && isset($user->positions))
                                                            <div class="researcher-positions mt-2">
                                                                <strong>Positions:</strong>
                                                                <ul class="mb-0 ps-3">
                                                                    @foreach($user->positions as $position)
                                                                        <li class="text-muted">
                                                                            {{ $position['type'] }} - {{ $position['lab_name'] }}
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endif

                                                        @if(isset($user->program))
                                                            <span class="researcher-program text-muted d-block">
                                                                {{ $user->program->program_name_en }}
                                                            </span>
                                                        @endif

                                                        @if(isset($user->belong_to))
                                                            <span class="researcher-program text-muted d-block">
                                                                {{ $user->belong_to }}
                                                            </span>
                                                        @endif

                                                        @if(isset($user->email))
                                                            <a href="mailto:{{ $user->email }}" class="researcher-email d-block text-primary">
                                                                <ion-icon name="mail-outline" class="align-middle me-1"></ion-icon>
                                                                {{ $user->email }}
                                                            </a>
                                                        @endif

                                                        @if(isset($user->expertise))
                                                            <div class="research-interests mt-2">
                                                                <h6 class="fw-bold mb-2">Research Interests</h6>
                                                                <div class="expertise-tags">
                                                                    @foreach($user->expertise->take(3) as $expertise)
                                                                        <span class="badge bg-light text-primary px-2 py-1">
                                                                            {{ $expertise->expert_name }}
                                                                        </span>
                                                                    @endforeach

                                                                    @if($user->expertise->count() > 3)
                                                                        <div class="readmore-content d-none">
                                                                            @foreach($user->expertise->slice(3) as $expertise)
                                                                                <span class="badge bg-light text-primary px-2 py-1">
                                                                                    {{ $expertise->expert_name }}
                                                                                </span>
                                                                            @endforeach
                                                                        </div>

                                                                        <span class="readmore-toggle text-primary fw-bold" onclick="toggleReadmore(this)">
                                                                            +{{ $user->expertise->count() - 3 }} More
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="no-data-message text-center py-5">
                                    <div class="mb-3">
                                        <ion-icon name="alert-circle-outline" style="font-size: 3rem; color: #6c757d;"></ion-icon>
                                    </div>
                                    <h4 class="text-muted">ไม่มีข้อมูลนักวิจัยในหมวดหมู่นี้</h4>
                                    <p class="text-muted">ขออภัย ไม่พบข้อมูลนักวิจัยในหมวดหมู่ {{ strtoupper($roleData['role_name']) }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

<script>
    function scrollCategoryLeft() {
        const wrapper = document.getElementById("categoryWrapper");

        if (!wrapper) {
            console.error("Error: categoryWrapper not found in the DOM.");
            return;
        }

        console.log("Before Left Scroll - scrollLeft:", wrapper.scrollLeft);
        console.log("scrollWidth:", wrapper.scrollWidth, "clientWidth:", wrapper.clientWidth);

        if (wrapper.scrollLeft <= 0) {
            console.warn("Already at the leftmost position.");
            return;
        }

        wrapper.scrollBy({
            left: -300,
            behavior: "smooth"
        });

        setTimeout(() => {
            console.log("After Left Scroll - scrollLeft:", wrapper.scrollLeft);
        }, 500);
    }

    function scrollCategoryRight() {
        const wrapper = document.getElementById("categoryWrapper");

        if (!wrapper) {
            console.error("Error: categoryWrapper not found in the DOM.");
            return;
        }

        console.log("Before Right Scroll - scrollLeft:", wrapper.scrollLeft);
        console.log("scrollWidth:", wrapper.scrollWidth, "clientWidth:", wrapper.clientWidth);

        if (wrapper.scrollLeft + wrapper.clientWidth >= wrapper.scrollWidth) {
            console.warn("Already at the rightmost position.");
            return;
        }

        wrapper.scrollBy({
            left: 300,
            behavior: "smooth"
        });

        setTimeout(() => {
            console.log("After Right Scroll - scrollLeft:", wrapper.scrollLeft);
        }, 500);
    }

    // กำหนดให้ปุ่มใช้ฟังก์ชันที่ถูกต้อง
    document.querySelector(".arrow-left")?.addEventListener("click", scrollCategoryLeft);
    document.querySelector(".arrow-right")?.addEventListener("click", scrollCategoryRight);

    // เลื่อนหน้าไปยัง Category ที่เลือก
    function scrollToCategory(id) {
        // ลบคลาส active จากทุก category-item
        document.querySelectorAll('.category-item').forEach(el => el.classList.remove('active'));

        // เพิ่มคลาส active ให้กับ category-item ที่เลือก
        document.getElementById(id).classList.add('active');

        // ปิด (หุบ) Accordion ทุกตัวก่อน
        document.querySelectorAll('.accordion-collapse').forEach(el => {
            if (el.classList.contains('show')) {
                el.classList.remove('show');
            }
        });

        // หาและเปิด Accordion ที่ตรงกับ Category ที่เลือก
        const roleId = id.split('-')[1];
        const roleElement = document.querySelector(`#collapse${roleId}`);
        const accordionHeader = document.querySelector(`#heading${roleId}`);

        if (roleElement && accordionHeader) {
            // เปิด Accordion
            roleElement.classList.add('show');

            // หน่วงเวลาให้ Accordion เปิดก่อนแล้วค่อยเลื่อน
            setTimeout(() => {
                smoothScrollTo(accordionHeader.offsetTop - 100, 800); // ปรับระยะและเวลาการเลื่อน
            }, 300);

            // ปรับคลาสของปุ่ม Accordion ให้แสดงว่าเปิดอยู่
            const accordionButton = accordionHeader.querySelector('.accordion-button');
            if (accordionButton) {
                accordionButton.classList.remove('collapsed');
                accordionButton.setAttribute('aria-expanded', 'true');
            }
        }
    }

    // ฟังก์ชันเลื่อนหน้าอย่างนุ่มนวล
    function smoothScrollTo(targetPosition, duration) {
        const startPosition = window.scrollY;
        const distance = targetPosition - startPosition;
        let startTime = null;

        function animation(currentTime) {
            if (!startTime) startTime = currentTime;
            const elapsedTime = currentTime - startTime;
            const ease = easeOutCubic(elapsedTime, startPosition, distance, duration);

            window.scrollTo(0, ease);

            if (elapsedTime < duration) {
                requestAnimationFrame(animation);
            }
        }

        function easeOutCubic(t, b, c, d) {
            t /= d;
            t--;
            return c * (t * t * t + 1) + b;
        }

        requestAnimationFrame(animation);
    }

    // เปลี่ยนลูกศร
    function toggleAccordionIcon(button) {
        const icon = button.querySelector('.accordion-arrow ion-icon');
        const isExpanded = button.getAttribute('aria-expanded') === "true";

        if (isExpanded) {
            icon.setAttribute("name", "chevron-up-outline"); // เปลี่ยนเป็นลูกศรชี้ขึ้น
        } else {
            icon.setAttribute("name", "chevron-down-outline"); // เปลี่ยนเป็นลูกศรชี้ลง
        }
    }

    // Readmore
    function toggleReadmore(element) {
        let readmoreContent = element.previousElementSibling; // หา div ที่เก็บ Research Interests ที่ซ่อนอยู่
        if (readmoreContent) {
            if (readmoreContent.classList.contains('d-none')) {
                readmoreContent.classList.remove('d-none'); // แสดงหัวข้อที่ซ่อน
                element.textContent = "Show less"; // เปลี่ยนข้อความปุ่ม
            } else {
                readmoreContent.classList.add('d-none'); // ซ่อนหัวข้ออีกครั้ง
                let count = readmoreContent.querySelectorAll('.badge').length;
                element.textContent = `+${count} More`; // เปลี่ยนปุ่มกลับเป็น More
            }
        }
    }
</script>
@endsection
