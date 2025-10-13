<!-- HEADER START -->
<header class="main-header clearfix adminheader" id="header">
    @php
        $addSuperadminPermission = user()->permission('add_superadmin');
        $addPackagePermission = user()->permission('add_packages');
        $addCompanyPermission = user()->permission('add_companies');
        $appSettingPermission = user()->permission('manage_superadmin_app_settings');
    @endphp

    <!-- NAVBAR LEFT(MOBILE MENU COLLAPSE) START-->
    <div class="navbar-left float-left d-flex align-items-center">
        <x-app-title class="d-none d-lg-flex" :pageTitle="$pageTitle"></x-app-title>

        <div class="d-block d-lg-none menu-collapse cursor-pointer position-relative" onclick="openMobileMenu()">
            <div class="mc-wrap">
                <div class="mcw-line"></div>
                <div class="mcw-line center"></div>
                <div class="mcw-line"></div>
            </div>
        </div>

        {{-- @if ($checkListCompleted < $checkListTotal && App::environment('codecanyon'))
            <div class="ml-3 d-none d-lg-block d-md-block">
                <span class="f-12 mb-1"><a href="{{ route('superadmin.checklist') }}" class="text-lightest ">
                        @lang('modules.accountSettings.setupProgress')</a>
                    <span class="float-right">{{ $checkListCompleted }}/{{ $checkListTotal }}</span>
                </span>
                <div class="progress" style="height: 5px; width: 150px">
                    <div class="progress-bar" role="progressbar"
                        style="width: {{ ($checkListCompleted / $checkListTotal) * 100 }}%;" aria-valuenow="25"
                        aria-valuemin="0" aria-valuemax="100">&nbsp;
                    </div>
                </div>
            </div>
        @endif --}}

    </div>

    <!-- NAVBAR LEFT(MOBILE MENU COLLAPSE) END-->
    <!-- NAVBAR RIGHT(SEARCH, ADD, NOTIFICATION, LOGOUT) START-->
    <div class="page-header-right float-right d-flex align-items-center justify-content-end">

        <ul>

            <!-- SEARCH START -->
            <li data-toggle="tooltip" data-placement="top" title="{{ __('app.search') }}" class="d-none d-sm-block">
                <div class="d-flex align-items-center">
                    <a href="javascript:;" class="d-block header-icon-box open-search">
                        <div class="icon-box-content gradientHover">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <!-- 90deg = left→right in SVG -->
                                    <linearGradient id="paint1_linear_196_750" x1="0" y1="0"
                                        x2="20" y2="0" gradientUnits="userSpaceOnUse">
                                        <stop offset="0%" stop-color="var(--gradientColor1, #7E7E7E)" />
                                        <stop offset="33.96%" stop-color="var(--gradientColor2, #7E7E7E)" />
                                        <stop offset="72.98%" stop-color="var(--gradientColor3, #7E7E7E)" />
                                        <stop offset="100%" stop-color="var(--gradientColor4, #7E7E7E)" />
                                    </linearGradient>
                                </defs>

                                <path
                                    d="M9.16667 15.8333C12.8486 15.8333 15.8333 12.8486 15.8333 9.16667C15.8333 5.48477 12.8486 2.5 9.16667 2.5C5.48477 2.5 2.5 5.48477 2.5 9.16667C2.5 12.8486 5.48477 15.8333 9.16667 15.8333Z"
                                    stroke="url(#paint1_linear_196_750)" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M17.5 17.5L13.875 13.875" stroke="url(#paint1_linear_196_750)" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </a>
                </div>
            </li>

            <!-- START TIMER -->
            @if (in_array('timelogs', user_modules()) &&
                    (add_timelogs_permission() == 'all' ||
                        add_timelogs_permission() == 'added' ||
                        manage_active_timelogs() == 'all'))
                <li data-toggle="tooltip" data-placement="top" title="{{ __('modules.timeLogs.startTimer') }}"
                    class="d-none d-sm-block">
                    <div class="d-flex align-items-center add_box dropdown">
                        <a href="#" class="d-block dropdown-toggle header-icon-box" type="link"
                            id="show-active-timer" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="icon-box-content gradientHover">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <ellipse cx="10" cy="10.8334" rx="7.5" ry="7.5"
                                        stroke="url(#paint0_linear_196_750)" stroke-width="1.5"
                                        stroke-linecap="round" />
                                    <path d="M4.16667 15.8334L2.5 17.5M15.8333 15.8334L17.5 17.5"
                                        stroke="url(#paint1_linear_196_750)" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path
                                        d="M15.8337 2.97475L16.3299 2.72663C17.0343 2.37443 17.2989 2.41474 17.8589 2.97475C18.4189 3.53475 18.4592 3.79937 18.107 4.50375L17.8589 5M4.16699 2.97475L3.67075 2.72663C2.96636 2.37443 2.70175 2.41474 2.14174 2.97475C1.58174 3.53475 1.54143 3.79937 1.89362 4.50375L2.14174 5"
                                        stroke="url(#paint2_linear_196_750)" stroke-width="1.5"
                                        stroke-linecap="round" />
                                    <path d="M10 7.91663V11.25L11.6667 12.9166" stroke="url(#paint3_linear_196_750)"
                                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M10 2.91663V1.66663" stroke="url(#paint4_linear_196_750)"
                                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M8.33301 1.66663H11.6663" stroke="url(#paint5_linear_196_750)"
                                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <defs>
                                        <linearGradient id="paint0_linear_196_750" x1="2.4911" y1="10.8378"
                                            x2="17.5" y2="10.8378" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="#7B79B8" />
                                            <stop offset="0.34" stop-color="#246DB5" />
                                            <stop offset="0.73" stop-color="#56A3D9" />
                                            <stop offset="1" stop-color="#68C18C" />
                                        </linearGradient>
                                        <linearGradient id="paint1_linear_196_750" x1="2.4911" y1="16.6672"
                                            x2="17.5" y2="16.6672" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="#7B79B8" />
                                            <stop offset="0.34" stop-color="#246DB5" />
                                            <stop offset="0.73" stop-color="#56A3D9" />
                                            <stop offset="1" stop-color="#68C18C" />
                                        </linearGradient>
                                        <linearGradient id="paint2_linear_196_750" x1="1.6571" y1="3.75074"
                                            x2="18.3337" y2="3.75074" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="#7B79B8" />
                                            <stop offset="0.34" stop-color="#246DB5" />
                                            <stop offset="0.73" stop-color="#56A3D9" />
                                            <stop offset="1" stop-color="#68C18C" />
                                        </linearGradient>
                                        <linearGradient id="paint3_linear_196_750" x1="9.99901" y1="10.4181"
                                            x2="11.6667" y2="10.4181" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="#7B79B8" />
                                            <stop offset="0.34" stop-color="#246DB5" />
                                            <stop offset="0.73" stop-color="#56A3D9" />
                                            <stop offset="1" stop-color="#68C18C" />
                                        </linearGradient>
                                        <linearGradient id="paint4_linear_196_750" x1="9.99941" y1="2.292"
                                            x2="11" y2="2.292" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="#7B79B8" />
                                            <stop offset="0.34" stop-color="#246DB5" />
                                            <stop offset="0.73" stop-color="#56A3D9" />
                                            <stop offset="1" stop-color="#68C18C" />
                                        </linearGradient>
                                        <linearGradient id="paint5_linear_196_750" x1="8.33103" y1="2.16692"
                                            x2="11.6663" y2="2.16692" gradientUnits="userSpaceOnUse">
                                            <stop stop-color="#7B79B8" />
                                            <stop offset="0.34" stop-color="#246DB5" />
                                            <stop offset="0.73" stop-color="#56A3D9" />
                                            <stop offset="1" stop-color="#68C18C" />
                                        </linearGradient>
                                    </defs>
                                </svg>
                                {{-- <span class="badge badge-primary active-timer-count position-absolute {{ $activeTimerCount == 0 ? 'd-none' : '' }}">{{ $activeTimerCount }}</span> --}}
                                {{-- <span class="pl-2 text">Start Timer</span> --}}
                            </div>
                        </a>
                        @if ($activeTimerCount == 0)
                            <!-- DROPDOWN - INFORMATION -->
                            <div class="dropdown-menu dropdown-menu-right" id="active-timer-list"
                                aria-labelledby="dropdownMenuLink" tabindex="0">
                                <a class="dropdown-item text-primary f-w-500" href="javascript:;"
                                    id="start-timer-modal">
                                    <i class="fa fa-play mr-2"></i>
                                    @lang('modules.timeLogs.startTimer')
                                </a>
                            </div>
                        @endif
                    </div>
                </li>
            @endif

            @if ($appSettingPermission == 'all')
                <!-- Sticky Note START -->
                {{-- <li data-toggle="tooltip" data-placement="top" title="{{ __('modules.accountSettings.clearCache') }}"
                    class="d-none d-sm-block cursor-pointer clear-cache">
                    <div class="d-flex align-items-center">
                        <span class="d-block header-icon-box">
                            <i class="fa fa-eraser f-16 "></i>
                        </span>
                    </div>
                </li> --}}
            @endif

            <!-- Sticky Note START -->

            <li data-toggle="tooltip" data-placement="top" title="{{ __('app.menu.stickyNotes') }}"
                class="d-none d-sm-block">
                <div class="d-flex align-items-center">
                    <a href="{{ route('sticky-notes.index') }}" class="d-block header-icon-box openRightModal">
                        <div class="icon-box-content gradientHover">
                            <svg width="19" height="20" viewBox="0 0 19 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M12.8333 1.66663V3.33329M8.66667 1.66663V3.33329M4.5 1.66663V3.33329"
                                    stroke="url(#paint0_linear_196_298)" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path
                                    d="M15.7497 8.33333C15.7497 5.58347 15.7497 4.20854 14.8954 3.35427C14.0411 2.5 12.6662 2.5 9.91634 2.5H7.41634C4.66648 2.5 3.29155 2.5 2.43728 3.35427C1.58301 4.20854 1.58301 5.58347 1.58301 8.33333V12.5C1.58301 15.2499 1.58301 16.6248 2.43728 17.4791C3.29155 18.3333 4.66648 18.3333 7.41634 18.3333H9.91634"
                                    stroke="url(#paint1_linear_196_298)" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M14.0833 11.6666L14.0833 18.3333M17.4167 15L10.75 15"
                                    stroke="url(#paint2_linear_196_298)" stroke-width="1.5" stroke-linecap="round" />
                                <path d="M5.33301 12.5H8.66634M5.33301 8.33337H11.9997"
                                    stroke="url(#paint3_linear_196_298)" stroke-width="1.5" stroke-linecap="round" />
                                <defs>
                                    <linearGradient id="paint0_linear_196_298" x1="4.49505" y1="2.50045"
                                        x2="12.8333" y2="2.50045" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#7B79B8" />
                                        <stop offset="0.34" stop-color="#246DB5" />
                                        <stop offset="0.73" stop-color="#56A3D9" />
                                        <stop offset="1" stop-color="#68C18C" />
                                    </linearGradient>
                                    <linearGradient id="paint1_linear_196_298" x1="1.5746" y1="10.4214"
                                        x2="15.7497" y2="10.4214" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#7B79B8" />
                                        <stop offset="0.34" stop-color="#246DB5" />
                                        <stop offset="0.73" stop-color="#56A3D9" />
                                        <stop offset="1" stop-color="#68C18C" />
                                    </linearGradient>
                                    <linearGradient id="paint2_linear_196_298" x1="10.746" y1="15.0019"
                                        x2="17.4167" y2="15.0019" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#7B79B8" />
                                        <stop offset="0.34" stop-color="#246DB5" />
                                        <stop offset="0.73" stop-color="#56A3D9" />
                                        <stop offset="1" stop-color="#68C18C" />
                                    </linearGradient>
                                    <linearGradient id="paint3_linear_196_298" x1="5.32905" y1="10.4179"
                                        x2="11.9997" y2="10.4179" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#7B79B8" />
                                        <stop offset="0.34" stop-color="#246DB5" />
                                        <stop offset="0.73" stop-color="#56A3D9" />
                                        <stop offset="1" stop-color="#68C18C" />
                                    </linearGradient>
                                </defs>
                            </svg>
                            {{-- <span class="pl-2 text-dark">04</span> --}}
                        </div>
                    </a>
                </div>
            </li>
            {{-- <li data-toggle="tooltip" data-placement="top" title="{{ __('app.menu.stickyNotes') }}"
                class="d-none d-sm-block">
                <div class="d-flex align-items-center">
                    <a href="{{ route('sticky-notes.index') }}" class="d-block header-icon-box openRightModal">
                        <i class="fa fa-sticky-note f-16 text-dark-grey"></i>
                    </a>
                </div>
            </li> --}}
            <!-- Sticky Note END -->
            <!-- NOTIFICATIONS START -->
            <li data-toggle="tooltip" data-placement="top" title="{{ __('app.newNotifications') }}"
                class="d-none d-sm-block">
                <div class="d-flex align-items-center">
                    <div class="dropdown notification_box">
                        <a href="javascript:;" class="d-block header-icon-box show-user-notifications"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="icon-box-content gradientHover">
                                <!-- Bell SVG with gradient stroke (no background) -->
                                <svg width="17" height="20" viewBox="0 0 17 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" aria-label="Notifications">
                                    <defs>
                                        <linearGradient id="paint1_linear_196_750" x1="0" y1="0"
                                            x2="17" y2="0" gradientUnits="userSpaceOnUse">
                                            <stop offset="0%" stop-color="var(--gradientColor1, #7B79B8)" />
                                            <stop offset="33.96%" stop-color="var(--gradientColor2, #246DB5)" />
                                            <stop offset="72.98%" stop-color="var(--gradientColor3, #56A3D9)" />
                                            <stop offset="100%" stop-color="var(--gradientColor4, #68C18C)" />
                                        </linearGradient>
                                    </defs>

                                    <path
                                        d="M2.79864 9.5758C2.73741 10.7391 2.8078 11.9774 1.76844 12.757C1.2847 13.1198 1 13.6892 1 14.2938C1 15.1256 1.6515 15.8333 2.5 15.8333H14.5C15.3485 15.8333 16 15.1256 16 14.2938C16 13.6892 15.7153 13.1198 15.2316 12.757C14.1922 11.9774 14.2626 10.7391 14.2014 9.57581C14.0418 6.54348 11.5365 4.16663 8.5 4.16663C5.46348 4.16663 2.95824 6.54348 2.79864 9.5758Z"
                                        stroke="url(#paint1_linear_196_750)" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path
                                        d="M7.25 2.60413C7.25 3.29448 7.80964 4.16663 8.5 4.16663C9.19036 4.16663 9.75 3.29448 9.75 2.60413C9.75 1.91377 9.19036 1.66663 8.5 1.66663C7.80964 1.66663 7.25 1.91377 7.25 2.60413Z"
                                        stroke="url(#paint1_linear_196_750)" stroke-width="1.5" />
                                    <path
                                        d="M11 15.8334C11 17.2141 9.88071 18.3334 8.5 18.3334C7.11929 18.3334 6 17.2141 6 15.8334"
                                        stroke="url(#paint1_linear_196_750)" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                        </a>

                        <!-- DROPDOWN - INFORMATION -->
                        <div class="dropdown-menu dropdown-menu-right notification-dropdown border-0 shadow-lg py-0 bg-additional-grey"
                            tabindex="0">
                            <div
                                class="d-flex px-3 justify-content-between align-items-center border-bottom-grey py-1 bg-white">
                                <div class="___class_+?50___">
                                    <p class="f-14 mb-0 text-dark f-w-500">@lang('app.newNotifications')</p>
                                </div>
                                @if ($unreadNotificationCount > 0)
                                    <div class="f-12 ">
                                        <a href="javascript:;"
                                            class="text-dark-grey mark-notification-read">@lang('app.markRead')</a> |
                                        <a href="{{ route('all-notifications') }}"
                                            class="text-dark-grey">@lang('app.showAll')</a>
                                    </div>
                                @endif
                            </div>
                            <div id="notification-list"></div>
                            @if ($unreadNotificationCount > 6)
                                <div class="d-flex px-3 pb-1 pt-2 justify-content-center bg-additional-grey">
                                    <a href="{{ route('all-notifications') }}"
                                        class="text-darkest-grey f-13">@lang('app.showAll')</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </li>
            <!-- NOTIFICATIONS END -->
            <!-- ADD START -->
            @if ($addSuperadminPermission == 'all' || $addPackagePermission == 'all' || $addCompanyPermission == 'all')
                <li data-toggle="tooltip" data-placement="top" title="{{ __('app.createNew') }}">
                    <div class="add_box dropdown header-icon-box">
                        <a class="d-block dropdown-toggle icon-box-content gradientHover" type="link"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <!-- Plus-in-square with gradient stroke -->
                            <svg width="21" height="22" viewBox="0 0 21 22" fill="none"
                                xmlns="http://www.w3.org/2000/svg" aria-label="Create">
                                <defs>
                                    <linearGradient id="paint1_linear_196_750" x1="0" y1="0"
                                        x2="21" y2="0" gradientUnits="userSpaceOnUse">
                                        <stop offset="0%" stop-color="var(--gradientColor1, #7B79B8)" />
                                        <stop offset="33.96%" stop-color="var(--gradientColor2, #246DB5)" />
                                        <stop offset="72.98%" stop-color="var(--gradientColor3, #56A3D9)" />
                                        <stop offset="100%" stop-color="var(--gradientColor4, #68C18C)" />
                                    </linearGradient>
                                </defs>
                                <path d="M10.4922 6.78943V15.2105M14.4922 11L6.49219 11"
                                    stroke="url(#paint1_linear_196_750)" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path
                                    d="M0.992188 11C0.992188 6.28595 0.992188 3.92893 2.38343 2.46447C3.77467 1 6.01384 1 10.4922 1C14.9705 1 17.2097 1 18.6009 2.46447C19.9922 3.92893 19.9922 6.28595 19.9922 11C19.9922 15.714 19.9922 18.0711 18.6009 19.5355C17.2097 21 14.9705 21 10.4922 21C6.01384 21 3.77467 21 2.38343 19.5355C0.992188 18.0711 0.992188 15.714 0.992188 11Z"
                                    stroke="url(#paint1_linear_196_750)" stroke-width="1.5" />
                            </svg>

                            Add
                            <!-- Chevron with gradient fill -->
                            <svg width="18" height="20" viewBox="0 0 21 22" fill="none"
                                xmlns="http://www.w3.org/2000/svg" aria-label="Chevron">
                                <defs>
                                    <linearGradient id="paint1_linear_196_750" x1="0" y1="0"
                                        x2="21" y2="0" gradientUnits="userSpaceOnUse">
                                        <stop offset="0%" stop-color="var(--gradientColor1, #7B79B8)" />
                                        <stop offset="33.96%" stop-color="var(--gradientColor2, #246DB5)" />
                                        <stop offset="72.98%" stop-color="var(--gradientColor3, #56A3D9)" />
                                        <stop offset="100%" stop-color="var(--gradientColor4, #68C18C)" />
                                    </linearGradient>
                                </defs>

                                <path d="M5 8 L10.5 13 L16 8" fill="none" stroke="url(#paint1_linear_196_750)"
                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>


                        </a>
                        <!-- DROPDOWN - INFORMATION -->
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink"
                            tabindex="0">
                            @if ($addCompanyPermission == 'all')
                                <a class="dropdown-item f-14 text-dark openRightModal"
                                    href="{{ route('superadmin.companies.create') }}">
                                    <i class="fa fa-plus f-w-500 mr-2 f-11"></i>
                                    @lang('superadmin.addCompany')
                                </a>
                            @endif
                            @if ($addPackagePermission == 'all')
                                <a class="dropdown-item f-14 text-dark openRightModal"
                                    href="{{ route('superadmin.packages.create') }}">
                                    <i class="fa fa-plus f-w-500 mr-2 f-11"></i>
                                    @lang('superadmin.addPackage')
                                </a>
                            @endif
                            @if ($addSuperadminPermission == 'all')
                                <a class="dropdown-item f-14 text-dark openRightModal"
                                    href="{{ route('superadmin.superadmin.create') }}">
                                    <i class="fa fa-plus f-w-500 mr-2 f-11"></i>
                                    @lang('superadmin.addSuperAdmin')
                                </a>
                            @endif
                        </div>

                    </div>
                </li>
            @endif
            <!-- ADD START -->
            @php
                $userName = user()->name;
            @endphp
            <!-- PROFILE START -->
            <li class="d-none d-sm-block" data-toggle="tooltip" data-placement="top"
                title="{{ __('app.profile') }}">
                <div
                    class="header-icon-box sidebar-brand-box dropdown cursor-pointer {{ user()->dark_theme ? 'bg-dark' : 'bg-light' }}">
                    <a href="#"
                        class="icon-box-content gradientHover dropdown-toggle sidebar-brand d-flex align-items-center justify-content-between w-100"
                        type="link" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false" role="button">
                        <div class="d-flex align-items-center">
                            {{-- Your profile SVG/icon --}}
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg" aria-label="Profile">
                                <defs>
                                    <linearGradient id="paint1_linear_196_750" x1="0" y1="0"
                                        x2="20" y2="0" gradientUnits="userSpaceOnUse">
                                        <stop offset="0%" stop-color="var(--gradientColor1, #7E7E7E)" />
                                        <stop offset="33.96%" stop-color="var(--gradientColor2, #7E7E7E)" />
                                        <stop offset="72.98%" stop-color="var(--gradientColor3, #7E7E7E)" />
                                        <stop offset="100%" stop-color="var(--gradientColor4, #7E7E7E)" />
                                    </linearGradient>
                                </defs>
                                <circle cx="10" cy="7" r="3" stroke="url(#paint1_linear_196_750)"
                                    stroke-width="2" />
                                <path d="M4 16c0-2.2 3.2-4 6-4s6 1.8 6 4" stroke="url(#paint1_linear_196_750)"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </a>

                    <!-- DROPDOWN - INFORMATION (same as the old logo menu) -->
                    <div class="dropdown-menu dropdown-menu-right sidebar-brand-dropdown ml-3"
                        aria-labelledby="dropdownMenuLink" tabindex="0">
                        <div class="d-flex justify-content-between align-items-center profile-box">
                            <a
                                @if (in_array('client', user_roles())) href="{{ route('profile-settings.index') }}"
             @elseif (user()->is_superadmin) href="{{ route('superadmin.settings.super-admin-profile.index') }}"
             @else href="{{ route('employees.show', user()->id) }}" @endif>
                                <div class="profileInfo d-flex align-items-center mr-1 flex-wrap">
                                    <div class="profileImg mr-2">
                                        <img class="h-100" src="{{ user()->image_url }}"
                                            alt="{{ data_get(session('clientContact'), 'contact_name', user()->name) }}">
                                    </div>
                                    <div class="ProfileData">
                                        <h3 class="f-15 f-w-500 text-dark" data-placement="bottom"
                                            data-toggle="tooltip"
                                            data-original-title="{{ data_get(session('clientContact'), 'contact_name', user()->name) }}">
                                            {{ data_get(session('clientContact'), 'contact_name', user()->name) }}
                                        </h3>
                                        <p class="mb-0 f-12 text-dark-grey">
                                            {{ user()->employeeDetail->designation->name ?? '' }}</p>
                                    </div>
                                </div>
                            </a>

                            @if (user()->is_superadmin)
                                <a href="{{ route('superadmin.settings.super-admin-profile.index') }}"
                                    data-toggle="tooltip" data-original-title="{{ __('app.menu.profileSettings') }}">
                                    <i class="side-icon bi bi-pencil-square"></i>
                                </a>
                            @else
                                <a href="{{ route('profile-settings.index') }}" data-toggle="tooltip"
                                    data-original-title="{{ __('app.menu.profileSettings') }}">
                                    <i class="side-icon bi bi-pencil-square"></i>
                                </a>
                            @endif
                        </div>

                        @if (checkCompanyCanAddMoreEmployees(user()->company_id))
                            @php $canAddEmp = isset($sidebarUserPermissions) ? ($sidebarUserPermissions['add_employees'] ?? 0) : 0; @endphp
                            @if (!in_array('client', user_roles()) && ($canAddEmp == 4 || $canAddEmp == 1) && in_array('employees', user_modules()))
                                <a class="dropdown-item d-flex justify-content-between align-items-center f-15 text-dark invite-member"
                                    href="javascript:;">
                                    <span>@lang('app.inviteMember') {{ $companyName ?? '' }}</span>
                                    <i class="side-icon bi bi-person-plus"></i>
                                </a>
                            @endif
                        @endif

                        <a class="dropdown-item d-flex justify-content-between align-items-center f-15 text-dark"
                            href="javascript:;">
                            <label for="dark-theme-toggle" class="mb-0">@lang('app.darkTheme')</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="dark-theme-toggle"
                                    @if (user()->dark_theme) checked @endif>
                                <label class="custom-control-label f-14" for="dark-theme-toggle"></label>
                            </div>
                        </a>

                        <a class="dropdown-item d-flex justify-content-between align-items-center f-15 text-dark"
                            href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            @lang('app.logout')
                            <i class="side-icon bi bi-power"></i>
                        </a>

                        @include('super-admin.sections.choose-company')
                    </div>
                </div>
            </li>
            <!-- PROFILE END -->
        </ul>
    </div>
    <!-- NAVBAR RIGHT(SEARCH, ADD, NOTIFICATION, LOGOUT) START-->
</header>
<!-- HEADER END -->

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<script>
    $(document).ready(function() {

        var runTimeClock = true;

        @if (isset($activeTimerCount))
            const activeTimerCount = parseInt("{{ $activeTimerCount }}");

            if (activeTimerCount > 0) {

                $('#show-active-timer').click(function() {
                    const url = "{{ route('timelogs.show_active_timer') }}";
                    $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                    $.ajaxModal(MODAL_XL, url);
                });

            }
        @endif


        $('#start-timer-modal').click(function() {
            const url = "{{ route('timelogs.show_timer') }}";
            $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_XL, url);
        });

        $('.open-search').click(function() {
            const url = "{{ route('search.index') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('.show-user-notifications').click(function() {
            const openStatus = $(this).attr('aria-expanded');

            if (typeof openStatus == "undefined" || openStatus == "false") {

                const token = '{{ csrf_token() }}';
                $.easyAjax({
                    type: 'POST',
                    url: "{{ route('show_notifications') }}",
                    container: "#notification-list",
                    blockUI: true,
                    data: {
                        '_token': token
                    },
                    success: function(data) {
                        if (data.status === 'success') {
                            $('#notification-list').html(data.html);
                        }
                    }
                });

            }

        });

        $('.mark-notification-read').click(function() {
            const token = '{{ csrf_token() }}';
            $.easyAjax({
                type: 'POST',
                url: "{{ route('mark_notification_read') }}",
                blockUI: true,
                data: {
                    '_token': token
                },
                success: function(data) {
                    if (data.status === 'success') {
                        $('#notification-list').html('');
                        $('.unread-notifications-count').remove();
                        window.location.reload();
                    }
                }
            });
        });

        $('.clear-cache').click(function() {
            $.easyAjax({
                type: 'GET',
                url: "{{ route('superadmin.superadmin.refresh-cache') }}",
                blockUI: true,
                success: function(data) {
                    if (data.status === 'success') {
                        window.location.reload();
                    }
                }
            });
        });

    });
</script>
