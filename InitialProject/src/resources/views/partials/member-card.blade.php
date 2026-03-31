{{--
    Reusable Member Card
    Params:
      $member          — User or Author model
      $isUser          — bool: true = User (has profile route), false = Author (external)
      $isHeadLab       — bool: show "Head LAB" badge (default false)
      $locale          — current locale (pass app()->getLocale())
--}}
@php
    $locale = $locale ?? app()->getLocale();
    $isUser = $isUser ?? true;
    $isHeadLab = $isHeadLab ?? false;
@endphp

<div class="member-card position-relative">
    @if($isHeadLab)
        <span class="head-lab-badge">Head LAB</span>
    @endif

    @if($isUser)
        <a href="{{ route('detail', Crypt::encrypt($member->id)) }}" class="profile-link d-block">
            <img
                src="{{ $member->picture ?? asset('img/default-profile.png') }}"
                alt="{{ $member->{'fname_'.$locale} }} {{ $member->{'lname_'.$locale} }}"
                class="center-image">
        </a>
        <div class="person-info mt-2">
            @if($locale === 'en' && isset($member->academic_ranks_en) && $member->academic_ranks_en === 'Lecturer' && ($member->doctoral_degree ?? '') === 'Ph.D.')
                <p>{{ $member->fname_en }} {{ $member->lname_en }}, Ph.D.</p>
            @elseif($locale === 'en' && isset($member->academic_ranks_en) && $member->academic_ranks_en === 'Lecturer')
                <p>{{ $member->fname_en }} {{ $member->lname_en }}</p>
            @elseif($locale === 'en' && ($member->doctoral_degree ?? '') === 'Ph.D.')
                <p>{{ str_replace('Dr.', '', $member->position_en ?? '') }} {{ $member->fname_en }} {{ $member->lname_en }}, Ph.D.</p>
            @else
                <p>{{ $member->{'position_'.$locale} ?? '' }} {{ $member->{'fname_'.$locale} }} {{ $member->{'lname_'.$locale} }}</p>
            @endif
            @if(!empty($member->email))
                <a href="mailto:{{ $member->email }}" class="email">{{ $member->email }}</a>
            @endif
        </div>
    @else
        {{-- External author / visiting scholar --}}
        <a class="profile-link d-block">
            <img
                src="{{ $member->picture ? asset('images/imag_user/' . $member->picture) : asset('img/default-profile.png') }}"
                alt="{{ $member->author_fname ?? '' }} {{ $member->author_lname ?? '' }}"
                class="center-image">
        </a>
        <div class="person-info mt-2">
            @if($locale === 'en')
                <p>{{ ($member->academic_ranks_en ?? '') ? ($member->academic_ranks_en . ' ') : '' }}{{ $member->author_fname }} {{ $member->author_lname }}</p>
            @else
                <p>{{ ($member->academic_ranks_th ?? '') ? ($member->academic_ranks_th . ' ') : '' }}{{ $member->author_fname }} {{ $member->author_lname }}</p>
            @endif
            @if(!empty($member->belong_to))
                <p class="text-muted" style="font-size:0.82rem;">{{ $member->belong_to }}</p>
            @endif
            @if(!empty($member->email))
                <a href="mailto:{{ $member->email }}" class="email">{{ $member->email }}</a>
            @endif
        </div>
    @endif
</div>
