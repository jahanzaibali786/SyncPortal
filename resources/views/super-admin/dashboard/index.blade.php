@extends('layouts.app')

@push('styles')
    <script src="{{ asset('vendor/jquery/frappe-charts.min.iife.js') }}"></script>
@endpush

@section('content')

    <!-- CONTENT WRAPPER START -->
    <div class="px-4 py-0 py-lg-4 border-top-0 super-admin-dashboard">
        <div class="row">
            @include('dashboard.update-message-dashboard')
            @includeIf('dashboard.update-message-module-dashboard')
            <x-cron-message :modal="true"></x-cron-message>
        </div>
        <!-- Bootstrap Tabs -->
        @php
            $tab = request('tab', 'overview'); // default
        @endphp
        {{-- <div class="pb-5">
            <div class="d-flex align-items-center justify-content-between">
                <!-- Bootstrap Tabs -->
                <ul class="nav nav-tabs border-0" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home"
                            type="button" role="tab">Overview</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile"
                            type="button" role="tab">Project</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact"
                            type="button" role="tab">Client</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact"
                            type="button" role="tab">HR</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact"
                            type="button" role="tab">Ticket</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact"
                            type="button" role="tab">Finance</button>
                    </li>
                </ul>

                <!-- Buttons -->
                <div class="d-flex gap-2 ms-3">
                    <button class="btn btn-gradient btn-rounded mr-2">
                        <svg width="18" height="20" viewBox="0 0 18 20" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M8.16699 10.8334H12.3337M5.66699 10.8334H5.67448M9.83366 14.1667H5.66699M12.3337 14.1667H12.3262"
                                stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M14 1.66663V3.33329M4 1.66663V3.33329" stroke="white" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <path
                                d="M1.08301 10.2027C1.08301 6.57161 1.08301 4.75607 2.12644 3.62803C3.16987 2.5 4.84925 2.5 8.20801 2.5H9.79134C13.1501 2.5 14.8295 2.5 15.8729 3.62803C16.9163 4.75607 16.9163 6.57161 16.9163 10.2027V10.6306C16.9163 14.2617 16.9163 16.0773 15.8729 17.2053C14.8295 18.3333 13.1501 18.3333 9.79134 18.3333H8.20801C4.84925 18.3333 3.16987 18.3333 2.12644 17.2053C1.08301 16.0773 1.08301 14.2617 1.08301 10.6306V10.2027Z"
                                stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M1.5 6.66663H16.5" stroke="white" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>

                        31 Aug 2025 to 06 Sep 2025
                    </button>
                    <button class="btn btn-gear btn-rounded">
                        <svg width="20" height="18" viewBox="0 0 20 18" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M12.9163 9.00004C12.9163 10.6109 11.6105 11.9167 9.99967 11.9167C8.38884 11.9167 7.08301 10.6109 7.08301 9.00004C7.08301 7.38921 8.38884 6.08337 9.99967 6.08337C11.6105 6.08337 12.9163 7.38921 12.9163 9.00004Z"
                                stroke="#8699B4" stroke-width="1.5" />
                            <path
                                d="M17.3497 10.7949C17.879 10.6209 18.1437 10.5339 18.2387 10.4027C18.3337 10.2715 18.3337 10.0607 18.3337 9.63909V8.36107C18.3337 7.93943 18.3337 7.72861 18.2387 7.59739C18.1437 7.46617 17.879 7.3792 17.3497 7.20526C15.8721 6.71969 14.9475 5.17585 15.2485 3.66574C15.3597 3.10822 15.4153 2.82946 15.348 2.6824C15.2808 2.53533 15.0965 2.43073 14.7281 2.22152L13.5807 1.57009C13.218 1.36417 13.0367 1.26121 12.8764 1.27849C12.7161 1.29578 12.512 1.47894 12.1037 1.84527C10.9194 2.90779 9.0826 2.90775 7.89831 1.84519C7.49001 1.47886 7.28586 1.2957 7.12558 1.27841C6.96529 1.26112 6.78394 1.36409 6.42126 1.57001L5.27391 2.22144C4.90546 2.43063 4.72124 2.53523 4.654 2.68227C4.58676 2.82931 4.64229 3.1081 4.75335 3.66569C5.05416 5.17585 4.12886 6.71974 2.65098 7.20528C2.12167 7.37918 1.85702 7.46613 1.762 7.59735C1.66699 7.72858 1.66699 7.93941 1.66699 8.36107V9.63909C1.66699 10.0607 1.66699 10.2716 1.76199 10.4028C1.85699 10.534 2.12165 10.621 2.65097 10.7949C4.12856 11.2805 5.05313 12.8243 4.7521 14.3344C4.64097 14.8919 4.5854 15.1707 4.65264 15.3178C4.71987 15.4648 4.90411 15.5694 5.27259 15.7786L6.41994 16.4301C6.78265 16.636 6.96401 16.739 7.12432 16.7217C7.28463 16.7044 7.48873 16.5212 7.89694 16.1548C9.08191 15.0913 10.9201 15.0913 12.105 16.1548C12.5132 16.5211 12.7173 16.7043 12.8777 16.7216C13.038 16.7389 13.2193 16.6359 13.582 16.43L14.7294 15.7786C15.0979 15.5693 15.2821 15.4647 15.3494 15.3176C15.4166 15.1705 15.361 14.8918 15.2498 14.3344C14.9486 12.8243 15.8724 11.2805 17.3497 10.7949Z"
                                stroke="#8699B4" stroke-width="1.5" stroke-linecap="round" />
                        </svg>

                        Dashboard Setting
                    </button>
                </div>
            </div>
        </div> --}}
        @if (user()->permission('view_companies'))
            <div class="row">
                @if ($sidebarSuperadminPermissions['view_companies'] != 5 && $sidebarSuperadminPermissions['view_companies'] != 'none')
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                        <x-cards.widget :title="__('superadmin.dashboard.totalCompany')" :value="$totalCompanies" icon="building" />
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                        <x-cards.widget :title="__('superadmin.dashboard.activeCompany')" :value="$activeCompanies" icon="store" />
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                        <x-cards.widget :title="__('superadmin.dashboard.licenseExpired')" :value="$expiredCompanies" icon="ban" />
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                        <x-cards.widget :title="__('superadmin.dashboard.inactiveCompany')" :value="$inactiveCompanies" icon="store-slash" />
                    </div>
                @endif
                @if ($sidebarSuperadminPermissions['view_packages'] != 5 && $sidebarSuperadminPermissions['view_packages'] != 'none')
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                        <x-cards.widget :title="__('superadmin.dashboard.totalPackages')" :value="$totalPackages" icon="boxes" />
                    </div>
                @endif
            </div>

            <div class="row">
                @if ($sidebarSuperadminPermissions['view_companies'] != 5 && $sidebarSuperadminPermissions['view_companies'] != 'none')
                    <div class="col-sm-12 col-lg-6 mt-4">
                        @include('super-admin.dashboard.recent-registered-companies')
                    </div>
                    <div class="col-sm-12 col-lg-6 mt-4">
                        @include('super-admin.dashboard.top-user-count-companies')
                    </div>
                @endif
                @if ($sidebarSuperadminPermissions['manage_billing'] != 5 && $sidebarSuperadminPermissions['manage_billing'] != 'none')
                    <div class="col-sm-12 col-lg-6 mt-4">
                        @include('super-admin.dashboard.recent-subscriptions')
                    </div>
                    <div class="col-sm-12 col-lg-6 mt-4">
                        @include('super-admin.dashboard.recent-license-expired')
                    </div>
                @endif
                @if ($sidebarSuperadminPermissions['view_packages'] != 5 && $sidebarSuperadminPermissions['view_packages'] != 'none')
                    <div class="col-sm-12 col-lg-6 mt-4">
                        @include('super-admin.dashboard.package-company-count')
                    </div>
                @endif
                @if ($sidebarSuperadminPermissions['view_companies'] != 5 && $sidebarSuperadminPermissions['view_companies'] != 'none')
                    <div class="col-sm-12 col-lg-6 mt-4">
                        @include('super-admin.dashboard.charts')
                    </div>
                @endif
            </div>
        @endif
    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
    <script>
        $('#registration_year').change(function() {
            const year = $(this).val();

            let url = `{{ route('superadmin.super_admin_dashboard') }}`;
            const string = `?year=${year}`;
            url += string;

            window.location.href = url;
        });
    </script>
@endpush
