@extends('layouts.app')

@push('datatable-styles')
    @include('sections.daterange_css')
@endpush

@push('styles')
    <style>
        .h-200 {
            height: 340px;
            overflow-y: auto;
        }

        .dashboard-settings {
            width: 600px;
        }

        @media (max-width: 768px) {
            .dashboard-settings {
                width: 300px;
            }
        }
    </style>
@endpush

@php
    $viewOverviewDashboard = user()->permission('view_overview_dashboard');
    $viewProjectDashboard = user()->permission('view_project_dashboard');
    $viewClientDashboard = user()->permission('view_client_dashboard');
    $viewHRDashboard = user()->permission('view_hr_dashboard');
    $viewTicketDashboard = user()->permission('view_ticket_dashboard');
    $viewFinanceDashboard = user()->permission('view_finance_dashboard');
@endphp

@section('filter-section')
    <!-- FILTER START -->
    <!-- DASHBOARD HEADER START -->
    <div class="d-flex filter-box project-header bg-white dashboard-header justify-content-between"
        style="height: 70px !important;">

        <div class="mobile-close-overlay w-100 h-100" id="close-client-overlay"></div>
        {{-- <div class="project-menu d-lg-flex" id="mob-client-detail">

            <a class="d-none close-it" href="javascript:;" id="close-client-detail">
                <i class="fa fa-times"></i>
            </a>

            @if ($viewOverviewDashboard == 'all')
                <x-tab :href="route('dashboard.advanced').'?tab=overview'" :text="__('modules.projects.overview')"
                       class="overview" ajax="false"/>
            @endif

            @if (in_array('projects', user_modules()) && $viewProjectDashboard == 'all')
                <x-tab :href="route('dashboard.advanced').'?tab=project'" :text="__('app.project')" class="project"
                       ajax="false"/>
            @endif

            @if (in_array('clients', user_modules()) && $viewClientDashboard == 'all')
                <x-tab :href="route('dashboard.advanced').'?tab=client'" :text="__('app.client')" class="client"
                       ajax="false"/>
            @endif

            @if ($viewHRDashboard == 'all' && (in_array('employees', user_modules()) || in_array('leaves', user_modules()) || in_array('attendance', user_modules())))
                <x-tab :href="route('dashboard.advanced').'?tab=hr'" :text="__('app.menu.hr')" class="hr" ajax="false"/>
            @endif

            @if (in_array('tickets', user_modules()) && $viewTicketDashboard == 'all')
                <x-tab :href="route('dashboard.advanced').'?tab=ticket'" :text="__('app.menu.ticket')" class="ticket"
                       ajax="false"/>
            @endif

            @if ($viewFinanceDashboard == 'all' && (in_array('invoices', user_modules()) || in_array('estimates', user_modules()) || in_array('leads', user_modules())))
                <x-tab :href="route('dashboard.advanced').'?tab=finance'" :text="__('app.menu.finance')" class="finance"
                       ajax="false"/>
            @endif

        </div> --}}

        <div class="px-4 py-0 py-lg-3  border-top-0 admin-dashboard">
            <div class="pb-5">
                <div class="d-flex align-items-center justify-content-between">
                    <!-- Bootstrap Tabs -->
                    @php
                        $tab = request('tab', 'overview'); // default
                    @endphp

                    <ul class="nav nav-tabs border-0" id="advancedTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $tab === 'overview' ? 'active' : '' }}" id="overview-tab"
                                href="{{ route('dashboard.advanced', ['tab' => 'overview']) }}" role="tab"
                                aria-controls="overview" aria-selected="{{ $tab === 'overview' ? 'true' : 'false' }}">
                                Overview
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $tab === 'project' ? 'active' : '' }}" id="project-tab"
                                href="{{ route('dashboard.advanced', ['tab' => 'project']) }}" role="tab"
                                aria-controls="project" aria-selected="{{ $tab === 'project' ? 'true' : 'false' }}">
                                Project
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $tab === 'client' ? 'active' : '' }}" id="client-tab"
                                href="{{ route('dashboard.advanced', ['tab' => 'client']) }}" role="tab"
                                aria-controls="client" aria-selected="{{ $tab === 'client' ? 'true' : 'false' }}">
                                Client
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $tab === 'hr' ? 'active' : '' }}" id="hr-tab"
                                href="{{ route('dashboard.advanced', ['tab' => 'hr']) }}" role="tab" aria-controls="hr"
                                aria-selected="{{ $tab === 'hr' ? 'true' : 'false' }}">
                                HR
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $tab === 'ticket' ? 'active' : '' }}" id="ticket-tab"
                                href="{{ route('dashboard.advanced', ['tab' => 'ticket']) }}" role="tab"
                                aria-controls="ticket" aria-selected="{{ $tab === 'ticket' ? 'true' : 'false' }}">
                                Ticket
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $tab === 'finance' ? 'active' : '' }}" id="finance-tab"
                                href="{{ route('dashboard.advanced', ['tab' => 'finance']) }}" role="tab"
                                aria-controls="finance" aria-selected="{{ $tab === 'finance' ? 'true' : 'false' }}">
                                Finance
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="advancedTabContent">
                        <div class="tab-pane fade {{ $tab === 'overview' ? 'show active' : '' }}" id="overview"
                            role="tabpanel" aria-labelledby="overview-tab">
                            {{-- Overview content --}}
                        </div>
                        <div class="tab-pane fade {{ $tab === 'project' ? 'show active' : '' }}" id="project"
                            role="tabpanel" aria-labelledby="project-tab">
                            {{-- Project content --}}
                        </div>
                        <div class="tab-pane fade {{ $tab === 'client' ? 'show active' : '' }}" id="client"
                            role="tabpanel" aria-labelledby="client-tab">
                            {{-- Client content --}}
                        </div>
                        <div class="tab-pane fade {{ $tab === 'hr' ? 'show active' : '' }}" id="hr" role="tabpanel"
                            aria-labelledby="hr-tab">
                            {{-- HR content --}}
                        </div>
                        <div class="tab-pane fade {{ $tab === 'ticket' ? 'show active' : '' }}" id="ticket"
                            role="tabpanel" aria-labelledby="ticket-tab">
                            {{-- Ticket content --}}
                        </div>
                        <div class="tab-pane fade {{ $tab === 'finance' ? 'show active' : '' }}" id="finance"
                            role="tabpanel" aria-labelledby="finance-tab">
                            {{-- Finance content --}}
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="px-4 py-0 py-lg-3  border-top-0 admin-dashboard">
            <!-- DATE START -->
            <div class="d-flex gap-2 ms-3">
                <div
                    class="{{ request('tab') == 'overview' || request('tab') == '' ? 'd-none' : 'd-flex' }} align-items-center border-left-grey border-left-grey-sm-0 h-100 pl-4">
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
                        <div class="select-status">
                            <input
                                style="background: none !important; border: none !important; color: white !important; width: 100%;"
                                type="text" id="datatableRange2" placeholder="@lang('placeholders.dateRange')">
                        </div>
                    </button>
                </div>
                <!-- DATE END -->
                <!-- DASHBOARD SETTINGS START -->
                @if (isset($widgets) && in_array('admin', user_roles()))
                    <div class="admin-dash-settings">
                        <x-form id="dashboardWidgetForm" method="POST">
                            <div class="dropdown keep-open">
                                <a class="btn btn-gear btn-rounded" type="link" id="dropdownMenuLink"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <svg data-original-title="{{ __('modules.dashboard.dashboardWidgetsSettings') }}"
                                        data-toggle="tooltip" width="20" height="18" viewBox="0 0 20 18"
                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M12.9163 9.00004C12.9163 10.6109 11.6105 11.9167 9.99967 11.9167C8.38884 11.9167 7.08301 10.6109 7.08301 9.00004C7.08301 7.38921 8.38884 6.08337 9.99967 6.08337C11.6105 6.08337 12.9163 7.38921 12.9163 9.00004Z"
                                            stroke="#8699B4" stroke-width="1.5" />
                                        <path
                                            d="M17.3497 10.7949C17.879 10.6209 18.1437 10.5339 18.2387 10.4027C18.3337 10.2715 18.3337 10.0607 18.3337 9.63909V8.36107C18.3337 7.93943 18.3337 7.72861 18.2387 7.59739C18.1437 7.46617 17.879 7.3792 17.3497 7.20526C15.8721 6.71969 14.9475 5.17585 15.2485 3.66574C15.3597 3.10822 15.4153 2.82946 15.348 2.6824C15.2808 2.53533 15.0965 2.43073 14.7281 2.22152L13.5807 1.57009C13.218 1.36417 13.0367 1.26121 12.8764 1.27849C12.7161 1.29578 12.512 1.47894 12.1037 1.84527C10.9194 2.90779 9.0826 2.90775 7.89831 1.84519C7.49001 1.47886 7.28586 1.2957 7.12558 1.27841C6.96529 1.26112 6.78394 1.36409 6.42126 1.57001L5.27391 2.22144C4.90546 2.43063 4.72124 2.53523 4.654 2.68227C4.58676 2.82931 4.64229 3.1081 4.75335 3.66569C5.05416 5.17585 4.12886 6.71974 2.65098 7.20528C2.12167 7.37918 1.85702 7.46613 1.762 7.59735C1.66699 7.72858 1.66699 7.93941 1.66699 8.36107V9.63909C1.66699 10.0607 1.66699 10.2716 1.76199 10.4028C1.85699 10.534 2.12165 10.621 2.65097 10.7949C4.12856 11.2805 5.05313 12.8243 4.7521 14.3344C4.64097 14.8919 4.5854 15.1707 4.65264 15.3178C4.71987 15.4648 4.90411 15.5694 5.27259 15.7786L6.41994 16.4301C6.78265 16.636 6.96401 16.739 7.12432 16.7217C7.28463 16.7044 7.48873 16.5212 7.89694 16.1548C9.08191 15.0913 10.9201 15.0913 12.105 16.1548C12.5132 16.5211 12.7173 16.7043 12.8777 16.7216C13.038 16.7389 13.2193 16.6359 13.582 16.43L14.7294 15.7786C15.0979 15.5693 15.2821 15.4647 15.3494 15.3176C15.4166 15.1705 15.361 14.8918 15.2498 14.3344C14.9486 12.8243 15.8724 11.2805 17.3497 10.7949Z"
                                            stroke="#8699B4" stroke-width="1.5" stroke-linecap="round" />
                                    </svg>
                                    {{ __('Dashboard Settings') }}
                                </a>
                                <!-- Dropdown - User Information -->
                                <ul class="dropdown-menu dropdown-menu-right dashboard-settings p-20"
                                    aria-labelledby="dropdownMenuLink" tabindex="0">
                                    <li class="border-bottom mb-3">
                                        <h4 class="heading-h3">@lang('modules.dashboard.dashboardWidgets')</h4>
                                    </li>
                                    @php
                                        $userModules = user_modules();
                                    @endphp
                                    @foreach ($widgets as $widget)
                                        @php
                                            $wname = \Illuminate\Support\Str::camel($widget->widget_name);
                                            $moduleName = $widgetToModuleMap[$widget->widget_name] ?? null;
                                        @endphp
                                        @if ($moduleName && in_array($moduleName, $userModules))
                                            <li class="mb-2 float-left w-50">
                                                <div class="checkbox checkbox-info ">
                                                    <input id="{{ $widget->widget_name }}"
                                                        name="{{ $widget->widget_name }}" value="true"
                                                        @if ($widget->status) checked @endif type="checkbox">
                                                    <label for="{{ $widget->widget_name }}">@lang('modules.dashboard.' . $wname)</label>
                                                </div>
                                            </li>
                                        @endif
                                    @endforeach
                                    @if (count($widgets) % 2 != 0)
                                        <li class="mb-2 float-left w-50 height-35"></li>
                                    @endif
                                    <li class="float-none w-100">
                                        <x-forms.button-primary id="save-dashboard-widget"
                                            icon="check">@lang('app.save')
                                        </x-forms.button-primary>
                                    </li>
                                </ul>
                            </div>
                    </div>
                    </x-form>
                @endif
                <!-- DASHBOARD SETTINGS END -->
            </div>
        </div>

        {{-- <div class="ml-auto d-flex align-items-center justify-content-center ">

            <!-- DATE START -->
            <div
                class="{{ request('tab') == 'overview' || request('tab') == '' ? 'd-none' : 'd-flex' }} align-items-center border-left-grey border-left-grey-sm-0 h-100 pl-4">
                <i class="fa fa-calendar-alt mr-2 f-14 text-dark-grey"></i>
                <div class="select-status">
                    <input type="text"
                           class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                           id="datatableRange2" placeholder="@lang('placeholders.dateRange')">
                </div>
            </div>
            <!-- DATE END -->
            @if (isset($widgets) && in_array('admin', user_roles()))
                <div class="admin-dash-settings">
                    <x-form id="dashboardWidgetForm" method="POST">
                        <div class="dropdown keep-open">
                            <a class="d-flex align-items-center justify-content-center dropdown-toggle px-lg-4 border-left-grey text-dark"
                               type="link" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true"
                               aria-expanded="false">
                                <i class="fa fa-cog" data-original-title="{{__('modules.dashboard.dashboardWidgetsSettings')}}" data-toggle="tooltip"></i>
                            </a>
                            <!-- Dropdown - User Information -->
                            <ul class="dropdown-menu dropdown-menu-right dashboard-settings p-20"
                                aria-labelledby="dropdownMenuLink" tabindex="0">
                                <li class="border-bottom mb-3">
                                    <h4 class="heading-h3">@lang('modules.dashboard.dashboardWidgets')</h4>
                                </li>
                                @php
                                    $userModules = user_modules();
                                @endphp
                                @foreach ($widgets as $widget)
                                    @php
                                        $wname = \Illuminate\Support\Str::camel($widget->widget_name);
                                        $moduleName = $widgetToModuleMap[$widget->widget_name] ?? null;
                                    @endphp
                                    @if ($moduleName && in_array($moduleName, $userModules))
                                    <li class="mb-2 float-left w-50">
                                        <div class="checkbox checkbox-info ">
                                            <input id="{{ $widget->widget_name }}" name="{{ $widget->widget_name }}"
                                                   value="true" @if ($widget->status) checked @endif type="checkbox">
                                            <label for="{{ $widget->widget_name }}">@lang('modules.dashboard.' .
                                            $wname)</label>
                                        </div>
                                    </li>
                                    @endif
                                @endforeach
                                @if (count($widgets) % 2 != 0)
                                    <li class="mb-2 float-left w-50 height-35"></li>
                                @endif
                                <li class="float-none w-100">
                                    <x-forms.button-primary id="save-dashboard-widget" icon="check">@lang('app.save')
                                    </x-forms.button-primary>
                                </li>
                            </ul>
                        </div>
                    </x-form>
                </div>
            @endif

        </div>

        <a class="mb-0 d-block d-lg-none text-dark-grey mr-2 border-left-grey border-bottom-0"
           onclick="openClientDetailSidebar()"><i class="fa fa-ellipsis-v"></i></a> --}}

    </div>
    <!-- FILTER END -->
    <!-- DASHBOARD HEADER END -->
@endsection

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="px-4 py-0 py-lg-3  border-top-0 admin-dashboard">
        {{-- <div class="pb-5">
            <div class="pb-5">
                <div class="d-flex align-items-center justify-content-between">
                    <!-- Bootstrap Tabs -->
                    <ul class="nav nav-tabs border-0" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home"
                                type="button" role="tab"
                                onclick="window.location.href='{{ route('dashboard.advanced') }}?tab=overview'">
                                Overview
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile"
                                type="button" role="tab"
                                onclick="window.location.href='{{ route('dashboard.advanced') }}?tab=project'">
                                Project
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact"
                                type="button" role="tab"
                                onclick="window.location.href='{{ route('dashboard.advanced') }}?tab=client'">
                                Client
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact"
                                type="button" role="tab"
                                onclick="window.location.href='{{ route('dashboard.advanced') }}?tab=hr'">
                                HR
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact"
                                type="button" role="tab"
                                onclick="window.location.href='{{ route('dashboard.advanced') }}?tab=ticket'">
                                Ticket
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact"
                                type="button" role="tab"
                                onclick="window.location.href='{{ route('dashboard.advanced') }}?tab=finance'">
                                Finance
                            </button>
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
            </div>

        </div> --}}
        @include($view)
    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
    <script src="{{ asset('vendor/jquery/daterangepicker.min.js') }}"></script>
    <script type="text/javascript">
        $(function() {
            var format = '{{ company()->moment_date_format }}';
            var startDate = "{{ $startDate->format(company()->date_format) }}";
            var endDate = "{{ $endDate->format(company()->date_format) }}";
            var start = moment(startDate, format);
            var end = moment(endDate, format);

            $('#datatableRange2').daterangepicker({
                locale: daterangeLocale,
                linkedCalendars: false,
                startDate: start,
                endDate: end,
                ranges: daterangeConfig,
                opens: 'left',
                parentEl: '.dashboard-header'
            }, cb);


            $('#datatableRange2').on('apply.daterangepicker', function(ev, picker) {
                showTable();
            });

        });
    </script>


    <script>
        $(".dashboard-header").on("click", ".ajax-tab", function(event) {
            event.preventDefault();

            $('.project-menu .p-sub-menu').removeClass('active');
            $(this).addClass('active');

            const dateRangePicker = $('#datatableRange2').data('daterangepicker');
            let startDate = $('#datatableRange').val();

            let endDate;

            if (startDate === '') {
                startDate = null;
                endDate = null;
            } else {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            const requestUrl = this.href;

            $.easyAjax({
                url: requestUrl,
                blockUI: true,
                container: ".admin-dashboard",
                historyPush: true,
                data: {
                    startDate: startDate,
                    endDate: endDate
                },
                success: function(response) {
                    if (response.status === "success") {
                        $('.admin-dashboard').html(response.html);
                        init('.admin-dashboard');
                    }
                }
            });
        });

        $('.keep-open .dropdown-menu').on({
            "click": function(e) {
                e.stopPropagation();
            }
        });

        function showTable() {
            const dateRangePicker = $('#datatableRange2').data('daterangepicker');
            let startDate = '';
            let endDate = '';

            if (dateRangePicker) {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            // Get current URL and preserve the tab parameter
            const currentUrl = new URL(window.location.href);
            const tab = currentUrl.searchParams.get('tab') || 'overview';

            // Build the URL with date parameters for page reload
            const url = new URL('{{ route('dashboard.advanced') }}', window.location.origin);
            url.searchParams.set('tab', tab);

            if (startDate && endDate) {
                url.searchParams.set('startDate', startDate);
                url.searchParams.set('endDate', endDate);
            }

            // Reload the page with the new URL
            window.location.href = url.toString();
        }
    </script>
    <script>
        const activeTab = "{{ $activeTab }}";
        $('.project-menu .' + activeTab).addClass('active');
    </script>
@endpush
