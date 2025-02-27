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
        overflow-x: auto;
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
    <div class="row mb-5" style=" height: 250px; background-color: #1075BB; display:flex; flex-direction: column; justify-content: center; align-items: center; margin-bottom: 20px; box-sizing: border-box;">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 fw-bold mb-4" style="color: white;">OUR RESEARCHERS</h1>
            <form method="GET" action="{{ route('researchers.index') }}" class="search-form">
                <div class="input-group input-group-lg position-relative">
                    <input type="text" class="form-control border-2 shadow-none search-input"
                        name="textsearch" value="{{ $search ?? '' }}"
                        placeholder="Search researchers by name or interest..."
                        aria-label="Search researchers">
                    <button type="submit" class="search-button">
                        <ion-icon name="search" size="large" class="search-icon"></ion-icon>
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- Category Slider Section -->
    <div class="category-container">
        <button class="arrow" onclick="scrollCategoryLeft()">&#10094;</button>
        <div class="category-wrapper" id="categoryWrapper">
            @foreach($programs as $program)
            <div class="category-item" onclick="scrollToCategory('category-{{ $program->id }}')" id="category-{{ $program->id }}">
                {{ strtoupper($program->program_name_en) }}
            </div>
            @endforeach
        </div>
        <button class="arrow" onclick="scrollCategoryRight()">&#10095;</button>
    </div>

    <!-- Accordion Section -->
    <div class="accordion custom-accordion" id="programAccordion">
        @foreach($programs as $program)
        @if($program->users->count() > 0)
        <div class="accordion-item border-0 rounded-4 overflow-hidden">
            <h2 class="accordion-header" id="heading{{ $program->id }}">
                <button class="accordion-button custom-accordion-btn d-flex justify-content-between align-items-center px-4 py-3 w-100"
                    type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $program->id }}"
                    aria-expanded="{{ in_array($program->id, $expandedProgramIds) ? 'true' : 'false' }}"
                    aria-controls="collapse{{ $program->id }}"
                    onclick="toggleAccordionIcon(this)">

                    <div class="d-flex align-items-center">
                        <span class="fw-bold text-white me-3">{{ strtoupper($program->program_name_en) }}</span>
                        <span class="badge bg-white text-dark fw-bold">{{ $program->users->count() }}</span>
                    </div>

                    <div class="accordion-arrow">
                        <ion-icon name="chevron-down-outline"></ion-icon>
                    </div>
                </button>
            </h2>

            <div id="collapse{{ $program->id }}"
                class="accordion-collapse collapse {{ in_array($program->id, $expandedProgramIds) ? 'show' : '' }}"
                aria-labelledby="heading{{ $program->id }}">
                <div class="accordion-body p-4">
                    <div class="container">
                        <div class="row">
                            @foreach($program->users->where('is_research', 1) as $user)
                            <div class="col-md-6 mb-4"> <!-- 2 คอลัมน์ต่อแถว -->
                                <a href="{{ route('detail', Crypt::encrypt($user->id)) }}" class="text-decoration-none">
                                    <div class="researcher-card p-3 shadow-sm rounded-3 d-flex flex-column h-100">
                                        <div class="d-flex align-items-start">
                                            <img class="researcher-image me-3"
                                                src="{{ $user->picture ?? asset('img/default-profile.png') }}"
                                                alt="{{ $user->{'fname_'.app()->getLocale()} }}'s photo">

                                            <div class="researcher-info flex-grow-1">
                                                <span class="researcher-name d-block text-primary fw-bold">
                                                    {{ $user->{'fname_'.app()->getLocale()} }} {{ $user->{'lname_'.app()->getLocale()} }}
                                                </span>
                                                <span class="researcher-position text-muted">{{ $user->position_en }}</span>

                                                <a href="mailto:{{ $user->email }}" class="researcher-email d-block text-primary">
                                                    <ion-icon name="mail-outline" class="align-middle me-1"></ion-icon>
                                                    {{ $user->email }}
                                                </a>

                                                <div class="research-interests mt-2">
                                                    <h6 class="fw-bold mb-2">Research Interests</h6>
                                                    <div class="expertise-tags">
                                                        @foreach($user->expertise->take(3) as $expertise)
                                                        <span class="badge bg-light text-primary px-2 py-1">
                                                            {{ $expertise->expert_name }}
                                                        </span>
                                                        @endforeach

                                                        @if($user->expertise->count() > 3)
                                                        <!-- ซ่อน Research Interests ที่เกิน 3 หัวข้อ -->
                                                        <div class="readmore-content d-none">
                                                            @foreach($user->expertise->slice(3) as $expertise)
                                                            <span class="badge bg-light text-primary px-2 py-1">
                                                                {{ $expertise->expert_name }}
                                                            </span>
                                                            @endforeach
                                                        </div>

                                                        <!-- ปุ่มกด "More" -->
                                                        <span class="readmore-toggle text-primary fw-bold" onclick="toggleReadmore(this)">
                                                            +{{ $user->expertise->count() - 3 }} More
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>

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
    document.querySelector(".arrow-left").addEventListener("click", scrollCategoryLeft);
    document.querySelector(".arrow-right").addEventListener("click", scrollCategoryRight);

    //เลื่อนหน้าไปยัง Category ที่เลือก
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
        const programId = id.split('-')[1];
        const programElement = document.querySelector(`#collapse${programId}`);
        const accordionHeader = document.querySelector(`#heading${programId}`);

        if (programElement) {
            // เปิด Accordion
            programElement.classList.add('show');

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
@stop