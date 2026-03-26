@extends('layouts.layout')

@section('content')

{{-- ── Blue page-header banner ─────────────────────────────── --}}
<div class="page-header">
    <div class="container">
        <h1>OUR RESEARCHERS</h1>
        <form method="GET" action="{{ route('researchers.index') }}" class="search-form" id="researcherSearchForm">
            <div class="position-relative">
                <input
                    type="text"
                    id="researcherSearchInput"
                    class="form-control search-input"
                    name="textsearch"
                    value="{{ $search ?? '' }}"
                    placeholder="Search by name, expertise, or program..."
                    aria-label="Search researchers"
                >
                <button type="submit" class="search-btn">
                    <ion-icon name="search" size="large"></ion-icon>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── No Results ───────────────────────────────────────────── --}}
@if(isset($noResults) && $noResults)
<div class="no-results-message">
    <h3><ion-icon name="search-outline" class="me-2"></ion-icon> No Results Found</h3>
    <p>Sorry, we couldn't find any researchers matching "{{ $search }}".</p>
    <a href="{{ route('researchers.index') }}" class="btn btn-primary">
        <ion-icon name="refresh-outline" class="me-1"></ion-icon> Clear Search
    </a>
</div>

@else
{{-- ── Main Layout: Sidebar + Grid ─────────────────────────── --}}
<div class="researchers-layout container-fluid px-3 px-md-4 pb-5">

    {{-- ── Sidebar ─────────── --}}
    <aside class="researchers-sidebar" id="researchersSidebar">

        {{-- Mobile toggle --}}
        <button class="rs-mobile-toggle d-md-none mb-3" id="sidebarToggle" type="button">
            <i class="fa fa-filter me-2"></i> Filters
            <i class="fa fa-chevron-down ms-auto" id="sidebarToggleIcon"></i>
        </button>

        <div class="rs-sidebar-inner" id="sidebarInner">

            {{-- Role Filter --}}
            <div class="rs-filter-section">
                <div class="rs-filter-header" data-target="filterRole">
                    <span class="rs-filter-title">CATEGORY</span>
                    <i class="fa fa-chevron-up rs-filter-arrow" id="arrow-filterRole"></i>
                </div>
                <div class="rs-filter-body" id="filterRole">
                    <label class="rs-check-label">
                        <input type="checkbox" class="rs-role-filter" value="all" checked> All
                    </label>
                    @foreach($roleUsers as $roleId => $roleData)
                    <label class="rs-check-label">
                        <input type="checkbox" class="rs-role-filter" value="{{ $roleId }}">
                        {{ ucfirst($roleData['role_name']) }}
                        <span class="rs-count-badge">{{ $roleData['total_users'] }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Program Filter --}}
            @if(isset($programs) && $programs->isNotEmpty())
            <div class="rs-filter-section">
                <div class="rs-filter-header" data-target="filterProgram">
                    <span class="rs-filter-title">PROGRAM</span>
                    <i class="fa fa-chevron-up rs-filter-arrow" id="arrow-filterProgram"></i>
                </div>
                <div class="rs-filter-body" id="filterProgram">
                    @foreach($programs as $program)
                    <label class="rs-check-label">
                        <input type="checkbox" class="rs-program-filter" value="{{ $program->id }}">
                        {{ $program->program_name_en }}
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Academic Position Filter --}}
            <div class="rs-filter-section">
                <div class="rs-filter-header" data-target="filterPosition">
                    <span class="rs-filter-title">ACADEMIC POSITION</span>
                    <i class="fa fa-chevron-up rs-filter-arrow" id="arrow-filterPosition"></i>
                </div>
                <div class="rs-filter-body" id="filterPosition">
                    @php
                        $positions = [
                            'Prof. Dr.'      => 'Professor (Prof. Dr.)',
                            'Assoc. Prof. Dr.' => 'Assoc. Prof. Dr.',
                            'Asst. Prof. Dr.'  => 'Asst. Prof. Dr.',
                            'Assoc. Prof.'   => 'Associate Professor',
                            'Asst. Prof.'    => 'Assistant Professor',
                            'Lecturer'       => 'Lecturer',
                        ];
                    @endphp
                    @foreach($positions as $val => $label)
                    <label class="rs-check-label">
                        <input type="checkbox" class="rs-position-filter" value="{{ $val }}">
                        {{ $label }}
                    </label>
                    @endforeach

                </div>
            </div>

            {{-- Clear Filters --}}
            <button class="rs-clear-btn" id="clearFilters" type="button">
                <i class="fa fa-times me-1"></i> Clear Filters
            </button>

        </div>{{-- /.rs-sidebar-inner --}}
    </aside>

    {{-- ── Grid Section ─────── --}}
    <section class="researchers-grid-section">

        @foreach($roleUsers as $roleId => $roleData)
            @if($roleData['total_users'] > 0)
            <div class="rs-role-group" data-role="{{ $roleId }}">
                <h2 class="rs-role-title">
                    {{ strtoupper($roleData['role_name']) }}
                    <span class="rs-role-count">{{ $roleData['total_users'] }}</span>
                </h2>

                <div class="researchers-grid">
                    @foreach($roleData['users'] as $user)
                    @php
                        $isExternal = $roleId === 'external';
                        $detailUrl  = route('detail', Crypt::encrypt([
                            'type' => $isExternal ? 'author' : 'user',
                            'id'   => $user->id
                        ]));

                        $name = $isExternal
                            ? (($user->author_fname ?? '') . ' ' . ($user->author_lname ?? ''))
                            : (($user->{'fname_'.app()->getLocale()} ?? '') . ' ' . ($user->{'lname_'.app()->getLocale()} ?? ''));

                        $position = $user->position_en ?? ($user->researcher_type ?? '');

                        $department = isset($user->program) ? $user->program->program_name_en : ($user->belong_to ?? '');

                        // Picture path
                        if (!empty($user->picture)) {
                            $pic = $user->picture;
                            if (!str_contains($pic, '/') && !filter_var($pic, FILTER_VALIDATE_URL)) {
                                $pic = asset('images/imag_user/' . $pic);
                            } elseif (!filter_var($pic, FILTER_VALIDATE_URL)) {
                                $pic = asset($pic);
                            }
                        } else {
                            $pic = asset('img/default-profile.png');
                        }

                        $pubCount = isset($user->papers_count) ? $user->papers_count : null;
                        $defaultPic = asset('img/default-profile.png');
                    @endphp

                    <div class="rs-card is-clickable"
                         data-detail-url="{{ $detailUrl }}"
                         data-role="{{ $roleId }}"
                         data-position="{{ strtolower($position) }}"
                         data-program="{{ $user->program_id ?? '' }}"
                         tabindex="0"
                         role="link"
                         aria-label="View profile of {{ $name }}">

                        {{-- Photo --}}
                        <div class="rs-card-photo">
                            <img src="{{ $pic }}"
                                 alt="{{ $name }}"
                                 loading="lazy"
                                 onerror="this.src='{{ $defaultPic }}'">
                        </div>


                        {{-- Body --}}
                        <div class="rs-card-body">
                            <h3 class="rs-card-name">{{ $name }}</h3>

                            @if($position)
                            <p class="rs-card-position">{{ $position }}</p>
                            @endif

                            @if($department)
                            <p class="rs-card-dept">{{ $department }}</p>
                            @endif

                            @if(!$isExternal && isset($user->expertise) && $user->expertise->isNotEmpty())
                            <div class="rs-expertise-tags">
                                @foreach($user->expertise->take(3) as $exp)
                                <span class="rs-expertise-tag">{{ $exp->expert_name }}</span>
                                @endforeach
                                @if($user->expertise->count() > 3)
                                <button class="rs-more-btn" type="button"
                                    onclick="event.stopPropagation(); toggleExpertise(this)">
                                    +{{ $user->expertise->count() - 3 }} more
                                </button>
                                <div class="rs-expertise-extra d-none">
                                    @foreach($user->expertise->skip(3) as $exp)
                                    <span class="rs-expertise-tag">{{ $exp->expert_name }}</span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endif

                            @if(!$isExternal && isset($user->email) && $user->email)
                            <a href="mailto:{{ $user->email }}"
                               class="rs-card-email"
                               onclick="event.stopPropagation()">
                                <i class="fa fa-envelope me-1"></i>{{ $user->email }}
                            </a>
                            @endif
                        </div>

                        {{-- Stats footer --}}
                        @if(isset($pubCount))
                        <div class="rs-card-stats">
                            <div class="rs-stat">
                                <i class="fa fa-file-alt"></i>
                                <span>{{ $pubCount }}</span>
                                <small>Publications</small>
                            </div>
                        </div>
                        @endif

                    </div>{{-- /.rs-card --}}
                    @endforeach
                </div>{{-- /.researchers-grid --}}

                {{-- Pagination per role --}}
                @if($roleData['users']->hasPages())
                <div class="rs-pagination mt-3">
                    @include('partials.pagination', ['paginator' => $roleData['users']])
                </div>
                @endif

            </div>{{-- /.rs-role-group --}}
            @endif
        @endforeach

    </section>{{-- /.researchers-grid-section --}}

</div>{{-- /.researchers-layout --}}
@endif

<script>
(function () {
    // ── Card click → detail ──────────────────────────────────────
    document.querySelectorAll('.rs-card[data-detail-url]').forEach(card => {
        const go = () => window.location.href = card.dataset.detailUrl;
        card.addEventListener('click', e => {
            if (e.target.closest('a,button,.rs-more-btn')) return;
            go();
        });
        card.addEventListener('keydown', e => {
            if ((e.key === 'Enter' || e.key === ' ') && !e.target.closest('a,button')) {
                e.preventDefault(); go();
            }
        });
    });

    // ── Sidebar collapse sections ────────────────────────────────
    document.querySelectorAll('.rs-filter-header').forEach(header => {
        header.addEventListener('click', () => {
            const targetId = header.dataset.target;
            const body  = document.getElementById(targetId);
            const arrow = document.getElementById('arrow-' + targetId);
            if (!body) return;
            const isOpen = !body.classList.contains('rs-collapsed');
            body.classList.toggle('rs-collapsed', isOpen);
            if (arrow) arrow.classList.toggle('fa-chevron-down', isOpen);
            if (arrow) arrow.classList.toggle('fa-chevron-up',   !isOpen);
        });
    });

    // ── Mobile sidebar toggle ────────────────────────────────────
    const toggleBtn  = document.getElementById('sidebarToggle');
    const sidebarInner = document.getElementById('sidebarInner');
    const toggleIcon = document.getElementById('sidebarToggleIcon');
    if (toggleBtn && sidebarInner) {
        toggleBtn.addEventListener('click', () => {
            const open = sidebarInner.classList.toggle('rs-sidebar-open');
            if (toggleIcon) {
                toggleIcon.classList.toggle('fa-chevron-up',   open);
                toggleIcon.classList.toggle('fa-chevron-down', !open);
            }
        });
    }

    // ── Expertise "more" toggle ───────────────────────────────────
    window.toggleExpertise = function (btn) {
        const extra = btn.nextElementSibling;
        if (!extra) return;
        const hidden = extra.classList.toggle('d-none');
        const count  = extra.querySelectorAll('.rs-expertise-tag').length;
        btn.textContent = hidden ? `+${count} more` : 'Show less';
    };

    // ══════════════════════════════════════════════════════════════
    // ── Unified filter system (role + program + position) ────────
    // ══════════════════════════════════════════════════════════════

    function applyAllFilters() {
        // ── 1. Gather active filter values ──
        const roleChecked = [...document.querySelectorAll('.rs-role-filter:checked')]
            .map(c => c.value);
        const roleAll = roleChecked.includes('all') || roleChecked.length === 0;

        const posChecked = [...document.querySelectorAll('.rs-position-filter:checked')]
            .map(c => c.value.toLowerCase());
        const posAll = posChecked.length === 0;

        const progChecked = [...document.querySelectorAll('.rs-program-filter:checked')]
            .map(c => c.value);
        const progAll = progChecked.length === 0;

        // ── 2. Filter each role group ──
        document.querySelectorAll('.rs-role-group').forEach(group => {
            const groupRole = group.dataset.role;

            // Check if this role group passes the role filter
            const roleMatch = roleAll || roleChecked.includes(groupRole);

            if (!roleMatch) {
                // Entire group is hidden by role filter
                group.style.display = 'none';
                return;
            }

            // ── 3. Filter individual cards within visible groups ──
            let visibleCards = 0;
            group.querySelectorAll('.rs-card').forEach(card => {
                let show = true;

                // Position filter
                if (!posAll) {
                    const cardPos = (card.dataset.position || '').toLowerCase();
                    show = posChecked.some(p => cardPos.includes(p));
                }

                // Program filter
                if (show && !progAll) {
                    const cardProg = card.dataset.program || '';
                    show = progChecked.includes(cardProg);
                }

                card.style.display = show ? '' : 'none';
                if (show) visibleCards++;
            });

            // Hide the role group entirely if no cards are visible
            group.style.display = visibleCards > 0 ? '' : 'none';
        });
    }

    // ── Role filter checkboxes ───────────────────────────────────
    document.querySelectorAll('.rs-role-filter').forEach(cb => {
        cb.addEventListener('change', () => {
            if (cb.value === 'all' && cb.checked) {
                document.querySelectorAll('.rs-role-filter:not([value="all"])')
                    .forEach(c => c.checked = false);
            } else if (cb.value !== 'all') {
                document.querySelector('.rs-role-filter[value="all"]').checked = false;
                // If none checked, re-check "All"
                const anyChecked = document.querySelectorAll('.rs-role-filter:not([value="all"]):checked').length > 0;
                if (!anyChecked) {
                    document.querySelector('.rs-role-filter[value="all"]').checked = true;
                }
            }
            applyAllFilters();
        });
    });

    // ── Position filter checkboxes ────────────────────────────────
    document.querySelectorAll('.rs-position-filter').forEach(cb => {
        cb.addEventListener('change', applyAllFilters);
    });

    // ── Program filter checkboxes ─────────────────────────────────
    document.querySelectorAll('.rs-program-filter').forEach(cb => {
        cb.addEventListener('change', applyAllFilters);
    });

    // ── Clear all filters ─────────────────────────────────────────
    document.getElementById('clearFilters')?.addEventListener('click', () => {
        document.querySelectorAll('.rs-role-filter, .rs-position-filter, .rs-program-filter')
            .forEach(c => c.checked = false);
        const allCb = document.querySelector('.rs-role-filter[value="all"]');
        if (allCb) allCb.checked = true;
        // Reset visibility on all elements
        document.querySelectorAll('.rs-card, .rs-role-group')
            .forEach(el => el.style.display = '');
    });
})();
</script>
@endsection
