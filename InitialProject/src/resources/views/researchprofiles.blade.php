@extends('layouts.layout')

@php
    $educationItems = collect($res->education ?? []);
    $expertiseItems = collect($res->expertise ?? []);
    $yearOptions = array_reverse(json_decode($year, true) ?? []);
    $thaiFullName = trim(($res->position_th ?? '') . ' ' . ($res->fname_th ?? '') . ' ' . ($res->lname_th ?? ''));
    $englishFullName = trim(($res->fname_en ?? '') . ' ' . ($res->lname_en ?? ''));
    $englishFullName = ($res->doctoral_degree ?? null) === 'Ph.D.' ? trim($englishFullName . ', Ph.D.') : $englishFullName;
    $englishRank = $res->academic_ranks_en ?? '';
    $programName = data_get($res, 'program.program_name_en', '');
@endphp

<style>
    .research-profile-page {
        --rp-primary: var(--primary-color, #2563eb);
        --rp-primary-dark: var(--primary-hover, #1d4ed8);
        --rp-accent: #0ea5e9;
        --rp-soft: #eff6ff;
        --rp-slate: #475569;
        --rp-border: #e2e8f0;
        --rp-surface: #ffffff;
        --rp-surface-muted: #f8fafc;
        color: #0f172a;
        font-family: var(--body-font, 'Prompt', sans-serif);
    }

    .research-profile-page .profile-shell {
        display: flex;
        flex-direction: column;
        gap: 2rem;
        margin-top: 1rem;
    }

    .research-profile-page .profile-hero {
        position: relative;
        background: linear-gradient(135deg, #ffffff 0%, #f0f7ff 50%, #e0efff 100%);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 32px;
        box-shadow: 0 20px 40px -15px rgba(37, 99, 235, 0.1), 
                    inset 0 0 0 1px rgba(255,255,255,0.6);
        padding: 2.5rem;
        overflow: hidden;
    }

    .research-profile-page .profile-hero::before {
        content: '';
        position: absolute;
        top: -20%;
        right: -10%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(37,99,235,0.06) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .research-profile-page .profile-hero-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(280px, 0.85fr);
        gap: 1.75rem;
        align-items: start;
    }

    .research-profile-page .profile-main {
        display: flex;
        gap: 1.5rem;
        min-width: 0;
    }

    .research-profile-page .profile-photo-wrap {
        flex: 0 0 160px;
        position: relative;
    }

    .research-profile-page .profile-photo {
        width: 160px;
        height: 160px;
        object-fit: cover;
        border-radius: 50%;
        border: 5px solid #ffffff;
        box-shadow: 0 15px 35px rgba(29, 78, 216, 0.15),
                    0 0 0 2px rgba(37, 99, 235, 0.1);
        background: #f1f5f9;
        position: relative;
        z-index: 2;
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    .research-profile-page .profile-photo:hover {
        transform: scale(1.05);
    }

    .research-profile-page .profile-copy {
        min-width: 0;
    }

    .research-profile-page .profile-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 999px;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(14, 165, 233, 0.1));
        color: var(--rp-primary-dark);
        font-size: 0.85rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .research-profile-page .profile-name-th {
        margin: 0.5rem 0 0.3rem;
        font-size: clamp(1.8rem, 2.5vw, 2.25rem);
        font-weight: 700;
        line-height: 1.2;
        color: #0f172a;
        letter-spacing: -0.02em;
    }

    .research-profile-page .profile-name-en {
        margin: 0;
        font-size: clamp(1.2rem, 1.5vw, 1.4rem);
        font-weight: 500;
        color: var(--rp-slate);
    }

    .research-profile-page .profile-rank {
        margin: 0.75rem 0 0;
        font-size: 1rem;
        font-weight: 500;
        color: var(--rp-primary);
    }

    .research-profile-page .profile-links {
        display: flex;
        flex-wrap: wrap;
        gap: 0.85rem 1.5rem;
        margin-top: 1.25rem;
        padding-top: 1.25rem;
        border-top: 1px dashed rgba(203, 213, 225, 0.8);
    }

    .research-profile-page .profile-link-item {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        color: var(--rp-slate);
        font-size: 0.95rem;
        text-decoration: none;
        transition: color 0.2s ease, transform 0.2s ease;
    }
    
    .research-profile-page a.profile-link-item:hover {
        color: var(--rp-primary);
        transform: translateY(-1px);
    }

    .research-profile-page .profile-link-item strong {
        color: #0f172a;
        font-weight: 600;
    }

    .research-profile-page .profile-link-item.is-email {
        color: var(--rp-primary);
        font-weight: 600;
    }

    .research-profile-page .profile-chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        margin-top: 1rem;
    }

    .research-profile-page .profile-chip {
        padding: 0.35rem 0.85rem;
        border-radius: 8px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: var(--rp-slate);
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .research-profile-page .profile-chip:hover {
        background: var(--rp-primary);
        color: #fff;
        border-color: var(--rp-primary);
        transform: translateY(-1px);
    }

    .research-profile-page .profile-section-label {
        display: block;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        font-size: 0.86rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #64748b;
    }

    .research-profile-page .education-list {
        margin: 0;
        padding: 0;
        list-style: none;
        display: grid;
        gap: 0.75rem;
    }

    .research-profile-page .education-item {
        display: grid;
        grid-template-columns: 56px minmax(0, 1fr);
        gap: 0.85rem;
        align-items: start;
        padding: 0.85rem 1rem;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid rgba(219, 228, 240, 0.9);
    }

    .research-profile-page .education-year {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--rp-primary-dark);
    }

    .research-profile-page .education-text {
        font-size: 0.93rem;
        line-height: 1.55;
        color: #334155;
    }

    .research-profile-page .profile-side {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .research-profile-page .side-card {
        border: 1px solid var(--rp-border);
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.86);
        padding: 1rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
    }

    .research-profile-page .side-card-title {
        margin: 0 0 0.85rem;
        font-size: 0.9rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #475569;
    }

    .research-profile-page .filter-stack {
        display: grid;
        gap: 0.8rem;
    }

    .research-profile-page .filter-input-wrap {
        position: relative;
    }

    .research-profile-page .filter-input-wrap i {
        position: absolute;
        left: 0.95rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .research-profile-page .filter-control {
        width: 100%;
        min-height: 46px;
        border: 1px solid var(--rp-border);
        border-radius: 14px;
        background: var(--rp-surface-muted);
        color: #334155;
        font-size: 0.92rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .research-profile-page input.filter-control {
        padding: 0.8rem 1rem 0.8rem 2.7rem;
    }

    .research-profile-page select.filter-control {
        padding: 0.8rem 2.35rem 0.8rem 0.95rem;
        appearance: none;
    }

    .research-profile-page .filter-control:focus {
        outline: none;
        border-color: rgba(29, 78, 216, 0.45);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.09);
    }

    .research-profile-page .filter-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.8rem;
    }

    .research-profile-page .select-wrap {
        position: relative;
    }

    .research-profile-page .select-wrap i {
        position: absolute;
        right: 0.95rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
    }

    .research-profile-page .chart-card {
        background:
            linear-gradient(180deg, rgba(248, 250, 252, 0.95), rgba(241, 245, 249, 0.95));
    }

    .research-profile-page .chart-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.85rem;
    }

    .research-profile-page .chart-meta p {
        margin: 0;
        color: #64748b;
        font-size: 0.85rem;
    }

    .research-profile-page .chart-shell {
        position: relative;
        min-height: 220px;
    }

    .research-profile-page .profile-metrics {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }

    .research-profile-page .metric-card {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        border: 1px solid var(--rp-border);
        background: #fff;
        padding: 1.75rem 1.5rem;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        text-align: center;
    }

    .research-profile-page .metric-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 35px -5px rgba(37, 99, 235, 0.1);
        border-color: rgba(37, 99, 235, 0.2);
    }

    .research-profile-page .metric-card::after {
        content: "";
        position: absolute;
        inset: auto -15% -40% auto;
        width: 130px;
        height: 130px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.08) 0%, rgba(37, 99, 235, 0) 70%);
        transition: transform 0.3s ease;
    }
    
    .research-profile-page .metric-card:hover::after {
        transform: scale(1.1);
    }

    .research-profile-page .metric-value {
        position: relative;
        z-index: 1;
        margin: 0;
        font-size: clamp(2rem, 3.5vw, 2.75rem);
        font-weight: 800;
        line-height: 1;
        color: var(--rp-primary-dark);
        background: linear-gradient(135deg, var(--rp-primary-dark), var(--rp-primary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .research-profile-page .metric-label {
        position: relative;
        z-index: 1;
        margin: 0.75rem 0 0;
        font-size: 0.85rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--rp-slate);
    }

    .research-profile-page .profile-tabs-wrap.custom-tabs-wrap {
        padding: 0;
        background: transparent;
        gap: 1rem;
        margin-top: 1rem;
    }

    .research-profile-page .custom-tabs-nav {
        gap: 0.75rem;
        padding-bottom: 0;
        overflow-x: auto;
        flex-wrap: nowrap;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    
    .research-profile-page .custom-tabs-nav::-webkit-scrollbar {
        display: none;
    }

    .research-profile-page .custom-tab-btn {
        min-width: auto;
        padding: 0.8rem 1.5rem;
        border-radius: 12px;
        background: var(--rp-surface);
        border: 1px solid var(--rp-border);
        color: var(--rp-slate);
        font-size: 0.9rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        white-space: nowrap;
        transition: all 0.2s ease;
    }

    .research-profile-page .custom-tab-btn:hover {
        background: var(--rp-surface-muted);
        color: var(--rp-primary);
        border-color: rgba(37, 99, 235, 0.3);
    }

    .research-profile-page .custom-tab-btn.active {
        background: var(--rp-primary);
        color: #fff;
        border-color: var(--rp-primary);
        box-shadow: 0 8px 15px -3px rgba(37, 99, 235, 0.25);
    }

    .research-profile-page .btn-export {
        background: linear-gradient(135deg, #0f766e, #14b8a6);
        width: 50px;
        height: 50px;
        box-shadow: 0 12px 20px rgba(15, 118, 110, 0.22);
    }

    .research-profile-page .profile-results-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.1rem 1.2rem;
        border-radius: 22px;
        border: 1px solid var(--rp-border);
        background: var(--rp-surface);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
    }

    .research-profile-page .toolbar-search {
        position: relative;
        flex: 1 1 520px;
    }

    .research-profile-page .toolbar-search i {
        position: absolute;
        left: 0.95rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .research-profile-page .toolbar-search input {
        width: 100%;
        min-height: 48px;
        border-radius: 15px;
        border: 1px solid var(--rp-border);
        background: var(--rp-surface-muted);
        padding: 0.85rem 1rem 0.85rem 2.7rem;
    }

    .research-profile-page .toolbar-search input:focus {
        outline: none;
        background: #fff;
        border-color: rgba(29, 78, 216, 0.45);
        box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.09);
    }

    .research-profile-page .toolbar-length {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        flex-shrink: 0;
        color: #475569;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .research-profile-page .toolbar-length select {
        min-width: 78px;
        min-height: 42px;
        border-radius: 12px;
        border: 1px solid var(--rp-border);
        background: #fff;
        color: #0f172a;
        padding: 0.5rem 0.75rem;
    }

    .research-profile-page .profile-table-card {
        border-radius: 26px;
        border: 1px solid var(--rp-border);
        background: var(--rp-surface);
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .research-profile-page table.dataTable {
        margin: 0 !important;
        width: 100% !important;
    }

    .research-profile-page table.dataTable thead th {
        background: #eff6ff;
        border-bottom: 1px solid var(--rp-border) !important;
        color: #334155;
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 1rem 1.25rem !important;
        white-space: nowrap;
    }

    .research-profile-page table.dataTable tbody td {
        padding: 1rem 1.25rem !important;
        border-bottom: 1px solid #eef2f7;
        vertical-align: top;
        color: #334155;
    }

    .research-profile-page table.dataTable tbody tr:hover {
        background: #f8fbff;
    }

    .research-profile-page .paper-link {
        display: inline-block;
        color: var(--rp-primary);
        text-decoration: none;
        font-weight: 700;
        line-height: 1.5;
    }

    .research-profile-page .paper-link:hover {
        color: var(--rp-primary-dark);
        text-decoration: underline;
    }

    .research-profile-page .paper-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        align-items: center;
        margin-top: 0.5rem;
    }

    .research-profile-page .paper-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.28rem 0.6rem;
        border-radius: 999px;
        background: rgba(29, 78, 216, 0.09);
        color: var(--rp-primary);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    .research-profile-page .source-title {
        font-size: 0.82rem;
        color: #64748b;
        font-style: italic;
    }

    .research-profile-page .citation-count {
        font-weight: 700;
        color: #0f172a;
        text-align: right;
    }

    .research-profile-page .dataTables_wrapper .dataTables_paginate,
    .research-profile-page .dataTables_wrapper .dataTables_info {
        padding: 1rem 1.2rem 1.1rem;
    }

    .research-profile-page .dataTables_wrapper .dataTables_paginate .paginate_button {
        margin-left: 0.2rem;
    }

    .research-profile-page .dataTables_wrapper .dataTables_paginate .paginate_button .page-link,
    .research-profile-page .dataTables_wrapper .dataTables_paginate .pagination .page-item .page-link {
        border-radius: 10px !important;
    }

    .research-profile-page .dataTables_wrapper .dataTables_length,
    .research-profile-page .dataTables_wrapper .dataTables_filter {
        display: none;
    }

    .research-profile-page .empty-note {
        color: #94a3b8;
        font-style: italic;
    }

    @media (max-width: 1199px) {
        .research-profile-page .profile-hero-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 991px) {
        .research-profile-page .profile-metrics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767px) {
        .research-profile-page .profile-hero {
            padding: 1.25rem;
            border-radius: 22px;
        }

        .research-profile-page .profile-main {
            flex-direction: column;
        }

        .research-profile-page .profile-photo-wrap {
            flex-basis: auto;
        }

        .research-profile-page .profile-photo {
            width: 124px;
            height: 124px;
            border-radius: 28px;
        }

        .research-profile-page .filter-row,
        .research-profile-page .profile-results-toolbar {
            grid-template-columns: 1fr;
            flex-direction: column;
            align-items: stretch;
        }

        .research-profile-page .toolbar-length {
            justify-content: space-between;
        }

        .research-profile-page .custom-tabs-wrap {
            align-items: flex-start;
        }
    }

    @media (max-width: 575px) {
        .research-profile-page .profile-metrics {
            grid-template-columns: 1fr;
        }

        .research-profile-page .education-item {
            grid-template-columns: 1fr;
            gap: 0.35rem;
        }

        .research-profile-page table.dataTable thead th,
        .research-profile-page table.dataTable tbody td {
            padding: 0.85rem 0.9rem !important;
        }
    }
</style>

@section('content')
<div class="research-profile-page">
    <div class="profile-shell">
        <section class="profile-hero">
            <div class="profile-hero-grid">
                <div class="profile-main">
                    <div class="profile-photo-wrap">
                        <img
                            class="profile-photo"
                            src="{{ $res->profile_picture_url ?? ($res->picture ?? asset('img/default-profile.png')) }}"
                            alt="{{ $englishFullName ?: 'Researcher profile' }}"
                        >
                    </div>

                    <div class="profile-copy">
                        <span class="profile-kicker">
                            <i class="fa fa-user-circle-o" aria-hidden="true"></i>
                            Researcher Profile
                        </span>

                        @if($thaiFullName !== '')
                            <h1 class="profile-name-th">{{ $thaiFullName }}</h1>
                        @endif

                        <h2 class="profile-name-en">{{ $englishFullName }}</h2>

                        @if($englishRank !== '')
                            <p class="profile-rank">{{ $englishRank }}</p>
                        @endif

                        <div class="profile-links">
                            @if(!empty($res->email))
                                <a class="profile-link-item is-email" href="mailto:{{ $res->email }}">
                                    <i class="fa fa-envelope-o" aria-hidden="true"></i>
                                    <span>{{ $res->email }}</span>
                                </a>
                            @endif

                            @if(!empty($res->orcid))
                                <span class="profile-link-item">
                                    <i class="fa fa-id-card-o" aria-hidden="true"></i>
                                    <strong>ORCID:</strong>
                                    <span>{{ $res->orcid }}</span>
                                </span>
                            @endif

                            @if(!empty($programName))
                                <span class="profile-link-item">
                                    <i class="fa fa-university" aria-hidden="true"></i>
                                    <strong>Program:</strong>
                                    <span>{{ $programName }}</span>
                                </span>
                            @endif

                            @if(!empty($res->affiliation))
                                <span class="profile-link-item">
                                    <i class="fa fa-building-o" aria-hidden="true"></i>
                                    <strong>Affiliation:</strong>
                                    <span>{{ $res->affiliation }}</span>
                                </span>
                            @endif
                        </div>

                        @if($expertiseItems->isNotEmpty())
                            <span class="profile-section-label">{{ trans('message.expertise') }}</span>
                            <div class="profile-chip-list">
                                @foreach($expertiseItems as $expertise)
                                    <span class="profile-chip">{{ $expertise->expert_name }}</span>
                                @endforeach
                            </div>
                        @endif

                        <span class="profile-section-label">{{ trans('message.education') }}</span>
                        <ul class="education-list">
                            @forelse($educationItems as $edu)
                                <li class="education-item">
                                    <span class="education-year">{{ $edu->year }}</span>
                                    <span class="education-text">
                                        {{ $edu->qua_name }}
                                        @if(!empty($edu->uname))
                                            <br>{{ $edu->uname }}
                                        @endif
                                    </span>
                                </li>
                            @empty
                                <li class="education-item">
                                    <span class="education-text empty-note">No education records available.</span>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <aside class="profile-side">
                    <div class="side-card">
                        <h3 class="side-card-title">Find Publications</h3>
                        <div class="filter-stack">
                            <div class="filter-input-wrap">
                                <i class="fa fa-search" aria-hidden="true"></i>
                                <input
                                    id="profileQuickSearch"
                                    class="filter-control"
                                    type="text"
                                    placeholder="Search publications, year, or source..."
                                >
                            </div>

                            <div class="filter-row">
                                <div class="select-wrap">
                                    <select id="profileYearFilter" class="filter-control">
                                        <option value="">All Years</option>
                                        @foreach($yearOptions as $yearOption)
                                            <option value="{{ $yearOption }}">{{ $yearOption }}</option>
                                        @endforeach
                                    </select>
                                    <i class="fa fa-chevron-down" aria-hidden="true"></i>
                                </div>

                                <div class="select-wrap">
                                    <select id="profileCategoryFilter" class="filter-control">
                                        <option value="#home">Summary</option>
                                        <option value="#scopus">SCOPUS</option>
                                        <option value="#wos">Web of Science</option>
                                        <option value="#tci">TCI</option>
                                        <option value="#book">Books</option>
                                        <option value="#patent">Other Academic Works</option>
                                    </select>
                                    <i class="fa fa-chevron-down" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="side-card chart-card">
                        <div class="chart-meta">
                            <div>
                                <h3 class="side-card-title mb-1">Publication Trend</h3>
                                <p>Research output from {{ count($yearOptions) ? min($yearOptions) : '-' }} to {{ count($yearOptions) ? max($yearOptions) : '-' }}</p>
                            </div>
                        </div>
                        <div class="chart-shell">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="profile-metrics">
            <article class="metric-card">
                <p id="all" class="metric-value">0</p>
                <p class="metric-label">Summary</p>
            </article>
            <article class="metric-card">
                <p id="scopus_sum" class="metric-value">0</p>
                <p class="metric-label">Scopus</p>
            </article>
            <article class="metric-card">
                <p id="wos_sum" class="metric-value">0</p>
                <p class="metric-label">WOS</p>
            </article>
            <article class="metric-card">
                <p id="tci_sum" class="metric-value">0</p>
                <p class="metric-label">TCI</p>
            </article>
        </section>

        <div class="custom-tabs-wrap profile-tabs-wrap">
            <nav class="custom-tabs-nav" id="myTab" role="tablist">
                <button class="custom-tab-btn active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Summary</button>
                <button class="custom-tab-btn" id="scopus-tab" data-bs-toggle="tab" data-bs-target="#scopus" type="button" role="tab" aria-controls="scopus" aria-selected="false">SCOPUS</button>
                <button class="custom-tab-btn" id="wos-tab" data-bs-toggle="tab" data-bs-target="#wos" type="button" role="tab" aria-controls="wos" aria-selected="false">WEB OF SCIENCE</button>
                <button class="custom-tab-btn" id="tci-tab" data-bs-toggle="tab" data-bs-target="#tci" type="button" role="tab" aria-controls="tci" aria-selected="false">TCI</button>
                <button class="custom-tab-btn" id="book-tab" data-bs-toggle="tab" data-bs-target="#book" type="button" role="tab" aria-controls="book" aria-selected="false">หนังสือ</button>
                <button class="custom-tab-btn" id="patent-tab" data-bs-toggle="tab" data-bs-target="#patent" type="button" role="tab" aria-controls="patent" aria-selected="false">ผลงานวิชาการด้านอื่นๆ</button>
            </nav>

            @if($showExport)
                <a class="btn-export" href="{{ route('excel', ['id' => $res->id]) }}" target="_blank" aria-label="Export to Excel">
                    <img src="https://cdn-icons-png.flaticon.com/512/3405/3405255.png" alt="Export Icon" class="icon-export">
                </a>
            @endif
        </div>

        <div class="profile-results-toolbar">
            <div class="toolbar-search">
                <i class="fa fa-search" aria-hidden="true"></i>
                <input id="profileGlobalSearch" type="text" placeholder="ค้นหาชื่อ, ปี, หรือรายละเอียด...">
            </div>

            <label class="toolbar-length" for="profilePageLength">
                <span>แสดง</span>
                <select id="profilePageLength">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span>รายการต่อหน้า</span>
            </label>
        </div>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                <div class="profile-table-card">
                    <table id="example1" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Paper Name</th>
                                <th>Citations</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="scopus" role="tabpanel" aria-labelledby="scopus-tab">
                <div class="profile-table-card">
                    <table id="example2" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Paper Name</th>
                                <th>Citations</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="wos" role="tabpanel" aria-labelledby="wos-tab">
                <div class="profile-table-card">
                    <table id="example3" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Paper Name</th>
                                <th>Citations</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="tci" role="tabpanel" aria-labelledby="tci-tab">
                <div class="profile-table-card">
                    <table id="example4" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Paper Name</th>
                                <th>Citations</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="book" role="tabpanel" aria-labelledby="book-tab">
                <div class="profile-table-card">
                    <table id="example5" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Number</th>
                                <th>Year</th>
                                <th>Name</th>
                                <th>Author</th>
                                <th>สถานที่พิมพ์</th>
                                <th>Page</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="patent" role="tabpanel" aria-labelledby="patent-tab">
                <div class="profile-table-card">
                    <table id="example6" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Number</th>
                                <th>Name</th>
                                <th>Author</th>
                                <th>ประเภท</th>
                                <th>หมายเลขทะเบียน</th>
                                <th>วันที่จดทะเบียน</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>

<script>
    $(document).ready(function() {
        const profileId = '{{ $profileId }}';
        const profileType = '{{ $profileType }}';
        const userIdQuery = {!! isset($paperDetailUserId) ? "'?user_id=' + " . $paperDetailUserId : "''" !!};
        const quickSearchInput = $('#profileQuickSearch');
        const globalSearchInput = $('#profileGlobalSearch');
        const yearFilter = $('#profileYearFilter');
        const categoryFilter = $('#profileCategoryFilter');
        const pageLengthSelect = $('#profilePageLength');

        const tableConfig = {
            responsive: true,
            autoWidth: false,
            dom: 'rt<"d-flex flex-column flex-md-row justify-content-between align-items-center"ip>',
            pageLength: parseInt(pageLengthSelect.val(), 10),
            order: [[0, 'desc']],
            language: {
                info: 'แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ',
                infoEmpty: 'ไม่มีข้อมูลที่จะแสดง',
                zeroRecords: 'ไม่พบข้อมูลที่ค้นหา',
                paginate: {
                    first: 'หน้าแรก',
                    last: 'หน้าสุดท้าย',
                    next: 'ถัดไป',
                    previous: 'ก่อนหน้า'
                }
            }
        };

        const normalizePaperName = function(name) {
            return (name || '')
                .replace(/<inf>/g, '<sub>')
                .replace(/<\/inf>/g, '</sub>');
        };

        const paperColumns = [
            { data: 'paper_yearpub', width: '11%' },
            {
                data: null,
                width: '74%',
                render: function(data, type, row) {
                    const url = '/paper/' + row.id + '/detail' + userIdQuery;
                    return `
                        <div class="paper-content">
                            <a href="${url}" class="paper-link">${normalizePaperName(row.paper_name)}</a>
                            <div class="paper-meta">
                                ${row.paper_type ? `<span class="paper-badge">${row.paper_type}</span>` : ''}
                                ${row.paper_sourcetitle ? `<span class="source-title">${row.paper_sourcetitle}</span>` : ''}
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'paper_citation',
                className: 'citation-count',
                width: '15%',
                defaultContent: '0',
                render: function(data) {
                    return data || 0;
                }
            }
        ];

        const buildAuthors = function(row) {
            const authors = [];

            if (row.author) {
                row.author.forEach(function(author) {
                    authors.push(`${author.author_fname} ${author.author_lname}`);
                });
            }

            if (row.user) {
                row.user.forEach(function(user) {
                    authors.push(`${user.fname_en} ${user.lname_en}`);
                });
            }

            return authors.join(', ');
        };

        const t1 = $('#example1').DataTable({
            ...tableConfig,
            ajax: { url: `/profile/${profileId}/papers?type=${profileType}&source=all`, dataSrc: '' },
            columns: paperColumns
        });

        const t2 = $('#example2').DataTable({
            ...tableConfig,
            ajax: { url: `/profile/${profileId}/papers?type=${profileType}&source=scopus`, dataSrc: '' },
            columns: paperColumns
        });

        const t3 = $('#example3').DataTable({
            ...tableConfig,
            ajax: { url: `/profile/${profileId}/papers?type=${profileType}&source=wos`, dataSrc: '' },
            columns: paperColumns
        });

        const t4 = $('#example4').DataTable({
            ...tableConfig,
            ajax: { url: `/profile/${profileId}/papers?type=${profileType}&source=tci`, dataSrc: '' },
            columns: paperColumns
        });

        const t5 = $('#example5').DataTable({
            ...tableConfig,
            order: [[1, 'desc']],
            ajax: { url: `/profile/${profileId}/academic-works?type=${profileType}&work_type=book`, dataSrc: '' },
            columns: [
                {
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: 'ac_year',
                    render: function(data) {
                        return data ? data.substring(0, 4) : '';
                    }
                },
                { data: 'ac_name', defaultContent: '' },
                {
                    data: null,
                    render: function(data, type, row) {
                        return buildAuthors(row);
                    }
                },
                { data: 'ac_sourcetitle', defaultContent: '' },
                { data: 'ac_page', defaultContent: '' }
            ]
        });

        const t6 = $('#example6').DataTable({
            ...tableConfig,
            order: [[5, 'desc']],
            ajax: { url: `/profile/${profileId}/academic-works?type=${profileType}&work_type=other`, dataSrc: '' },
            columns: [
                {
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                { data: 'ac_name', defaultContent: '' },
                {
                    data: null,
                    render: function(data, type, row) {
                        return buildAuthors(row);
                    }
                },
                { data: 'ac_type', defaultContent: '' },
                { data: 'ac_refnumber', defaultContent: '' },
                { data: 'ac_year', defaultContent: '' }
            ]
        });

        const tableMap = {
            '#home': t1,
            '#scopus': t2,
            '#wos': t3,
            '#tci': t4,
            '#book': t5,
            '#patent': t6
        };

        const syncSearchInputs = function(value, source) {
            if (source !== 'quick') {
                quickSearchInput.val(value);
            }

            if (source !== 'global') {
                globalSearchInput.val(value);
            }
        };

        const applySearchToAllTables = function(value) {
            Object.values(tableMap).forEach(function(table) {
                table.search(value);
            });
        };

        const applyYearFilterToAllTables = function(value) {
            const exactValue = value ? '^' + $.fn.dataTable.util.escapeRegex(value) + '$' : '';

            t1.column(0).search(exactValue, true, false);
            t2.column(0).search(exactValue, true, false);
            t3.column(0).search(exactValue, true, false);
            t4.column(0).search(exactValue, true, false);
            t5.column(1).search(exactValue, true, false);
            t6.column(5).search(value || '', false, true);
        };

        const drawAllTables = function() {
            Object.values(tableMap).forEach(function(table) {
                table.draw(false);
            });
        };

        const activeTabTarget = function() {
            const activeButton = document.querySelector('#myTab .custom-tab-btn.active');
            return activeButton ? activeButton.getAttribute('data-bs-target') : '#home';
        };

        const syncCategoryFilter = function(target) {
            categoryFilter.val(target);
        };

        quickSearchInput.on('input', function() {
            const value = $(this).val();
            syncSearchInputs(value, 'quick');
            applySearchToAllTables(value);
            drawAllTables();
        });

        globalSearchInput.on('input', function() {
            const value = $(this).val();
            syncSearchInputs(value, 'global');
            applySearchToAllTables(value);
            drawAllTables();
        });

        yearFilter.on('change', function() {
            applyYearFilterToAllTables($(this).val());
            drawAllTables();
        });

        pageLengthSelect.on('change', function() {
            const pageLength = parseInt($(this).val(), 10);

            Object.values(tableMap).forEach(function(table) {
                table.page.len(pageLength);
                table.draw(false);
            });
        });

        categoryFilter.on('change', function() {
            const target = $(this).val();
            const trigger = document.querySelector(`[data-bs-target="${target}"]`);

            if (trigger) {
                bootstrap.Tab.getOrCreateInstance(trigger).show();
            }
        });

        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(event) {
            const target = $(event.target).attr('data-bs-target');
            syncCategoryFilter(target);

            if (tableMap[target]) {
                tableMap[target].columns.adjust().draw(false);
            }
        });

        syncCategoryFilter(activeTabTarget());
        applySearchToAllTables('');
        applyYearFilterToAllTables('');
        drawAllTables();
    });
</script>

<script>
    const year = {!! $year !!};
    const paperTci = {!! $paper_tci !!};
    const paperScopus = {!! $paper_scopus !!};
    const paperWos = {!! $paper_wos !!};
    const paperTciSummary = {!! $paper_tci_s !!};
    const paperScopusSummary = {!! $paper_scopus_s !!};
    const paperWosSummary = {!! $paper_wos_s !!};
    const paperBookSummary = {!! $paper_book_s !!};
    const paperPatentSummary = {!! $paper_patent_s !!};

    const sumArray = function(items) {
        return items.reduce(function(total, value) {
            return total + (parseInt(value, 10) || 0);
        }, 0);
    };

    const formatNumber = function(value) {
        return new Intl.NumberFormat('en-US').format(value);
    };

    document.getElementById('all').textContent = formatNumber(
        sumArray(paperScopusSummary) +
        sumArray(paperTciSummary) +
        sumArray(paperWosSummary) +
        sumArray(paperBookSummary) +
        sumArray(paperPatentSummary)
    );
    document.getElementById('scopus_sum').textContent = formatNumber(sumArray(paperScopusSummary));
    document.getElementById('wos_sum').textContent = formatNumber(sumArray(paperWosSummary));
    document.getElementById('tci_sum').textContent = formatNumber(sumArray(paperTciSummary));

    const chartContext = document.getElementById('barChart').getContext('2d');

    new Chart(chartContext, {
        type: 'bar',
        data: {
            labels: year,
            datasets: [
                {
                    label: 'SCOPUS',
                    backgroundColor: '#2563eb',
                    data: paperScopus
                },
                {
                    label: 'TCI',
                    backgroundColor: '#14b8a6',
                    data: paperTci
                },
                {
                    label: 'WOS',
                    backgroundColor: '#f59e0b',
                    data: paperWos
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    fontColor: '#475569',
                    padding: 16
                }
            },
            tooltips: {
                backgroundColor: '#0f172a',
                titleFontStyle: '600'
            },
            scales: {
                xAxes: [{
                    gridLines: {
                        display: false
                    },
                    ticks: {
                        fontColor: '#64748b'
                    }
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        precision: 0,
                        fontColor: '#64748b'
                    },
                    gridLines: {
                        color: 'rgba(148, 163, 184, 0.16)'
                    }
                }]
            }
        }
    });
</script>
@endsection
