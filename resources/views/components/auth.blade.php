<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $globalSetting->favicon_url }}">
    <link rel="manifest" href="{{ $globalSetting->favicon_url }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ $globalSetting->favicon_url }}">
    <meta name="theme-color" content="#ffffff">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/css/all.min.css') }}" defer="defer">

    <!-- Template CSS -->
    <link href="{{ asset('vendor/froiden-helper/helper.css') }}" rel="stylesheet" defer="defer">
    <link type="text/css" rel="stylesheet" media="all" href="{{ asset('css/main.css') }}">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <title>{{ $globalSetting->global_app_name ?? $globalSetting->app_name }}</title>


    @stack('styles')
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>

    <style defer="defer">
        .login_header {
            background-color: {{ $globalSetting->logo_background_color }} !important;
        }

        .change-lang {
            background-color: #e8eef3;
            padding: 0 3px 0 3px;
        }
    </style>
    @include('sections.theme_css')
    @if (file_exists(public_path() . '/css/login-custom.css'))
        <link href="{{ asset('css/login-custom.css') }}" rel="stylesheet">
    @endif

    @if (file_exists(public_path() . '/css/custom-css/theme-custom.css'))
        <link href="{{ asset('css/custom-css/theme-custom.css') }}" rel="stylesheet">
    @endif

    @if ($globalSetting->sidebar_logo_style == 'full')
        <style>
            .login_header img {
                max-width: unset;
            }
        </style>
    @endif

    @includeif('sections.custom_script')


</head>

<body
    class="{{ $globalSetting->auth_theme == 'dark' ? 'dark-theme' : '' }} {{ isRtl() ? (session('changedRtl') === false ? '' : 'rtl') : (session('changedRtl') == true ? 'rtl' : '') }}">

    <style>
        /* ==== FIXED HORIZONTAL LOGIN LAYOUT ==== */
        .login_section1 {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f5f5f5;
            min-height: 100vh;
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            overflow: hidden;
            width: 100vw;
        }

        .login_wrapper {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: stretch;
            width: 100%;
            max-width: 900px;
            /* optional: keep a consistent height */
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            box-sizing: border-box;
            padding-block: 1.5rem;
        }

        /* Equal halves, always side-by-side */
        .left_box,
        .right_box {
            flex: 1 1 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            min-width: 0;
            box-sizing: border-box;
        }

        .left_box {
            padding: 20px;
        }

        .left_box .login_box {
            width: 100%;
            /* max-width: 350px; */
            margin: auto;
        }

        .right_box img {
            width: 100%;
            height: auto;
            object-fit: cover;
        }

        /* No stacking, ever */
        @media (max-width: 1024px),
        (max-width: 768px),
        (max-width: 500px) {
            .login_wrapper {
                flex-direction: row !important;
                width: 900px;
                height: auto;
                margin: 0 auto;
                overflow: clip;
            }

            .left_box,
            .right_box {
                flex: 1 1 50% !important;
            }

            .left_box {
                padding: 10px;
            }
        }
    </style>


    <section class="py-5 bg-grey login_section1" style="height: 100vh; width: 100vw;"
        @if ($globalSetting->login_background_url) style="background: url('{{ $globalSetting->login_background_url }}') center center/cover no-repeat;" @endif>
        <div class=" bg-white d-flex flex-row justify-content-center align-items-stretch login_wrapper">

            <div class="col-6 p-0 d-flex justify-content-center align-items-center left_box" style="flex: 0 0 50%;">
                <div class="row w-100">
                    <div class="text-center col-md-12">

                        <div class="mx-auto text-center bg-white rounded login_box">
                            {{ $slot }}
                        </div>

                        {{ $outsideLoginBox ?? '' }}
                        {{-- @if ($languages->count() > 1)
                            <div class="my-3 d-flex flex-column flex-grow-1">
                                <div class="d-flex flex-wrap align-items-center justify-content-center">
                                    @foreach ($languages->take(4) as $index => $language)
                                        <span class="mx-3 my-10 f-12">
                                            <a href="javascript:;"
                                                class="text-dark-grey change-lang d-flex align-items-center"
                                                data-lang="{{ $language->language_code }}">
                                                <span
                                                    class="mr-2 flag-icon flag-icon-{{ $language->flag_code === 'en' ? 'gb' : $language->flag_code }} flag-icon-squared"></span>
                                                {{ \App\Models\LanguageSetting::LANGUAGES_TRANS[$language->language_code] ?? $language->language_name }}
                                            </a>
                                        </span>
                                    @endforeach

                                    @if ($languages->count() > 4)
                                        <div class="dropdown" style="z-index:10000">
                                            <a class="btn btn-lg f-14 px-2 py-1 text-dark-grey  rounded dropdown-toggle"
                                                type="button" id="languageDropdown" data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-ellipsis-h"></i>
                                            </a>

                                            <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                                aria-labelledby="languageDropdown"
                                                style="max-height: 600px; overflow-y: auto;">
                                                @foreach ($languages->slice(4) as $language)
                                                    <a class="dropdown-item change-lang" href="javascript:;"
                                                        data-lang="{{ $language->language_code }}">
                                                        <span
                                                            class="mr-2 flag-icon flag-icon-{{ $language->flag_code === 'en' ? 'gb' : $language->flag_code }} flag-icon-squared"></span>
                                                        {{ \App\Models\LanguageSetting::LANGUAGES_TRANS[$language->language_code] ?? $language->language_name }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif --}}

                    </div>
                </div>
            </div>
            <div class="col-6 p-0 right_box" style="flex: 0 0 50%;">
                <div class="swiper mySwiper h-100">
                    <div class="swiper-wrapper">
                        <!-- Slides -->
                        <div class="swiper-slide d-flex align-items-center justify-content-center">
                            <div class="d-flex align-items-center justify-content-center gap-2 text-center flex-column p-4 w-75"
                                style="gap: 1rem;">
                                <img src="{{ asset('images/sl2.png') }}" class="w-100 h-50 object-fit-cover"
                                    alt="Slide 1">
                                <div>
                                    <h3>Flexible and Scalable Solutions</h3>
                                    <p>
                                        Customizable features scale to meet your evolving demands.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide d-flex align-items-center justify-content-center">
                            <div class="d-flex align-items-center justify-content-center gap-2 text-center flex-column p-4 w-75"
                                style="gap: 1rem;">
                                <img src="{{ asset('images/sl3.png') }}" class="w-100 h-50 object-fit-cover"
                                    alt="Slide 2">
                                <div>
                                    <h3>Empowering Seamless Operations</h3>
                                    <p>
                                        Enhance efficiency and ensure smoother operations across every aspect of your
                                        organization.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide d-flex align-items-center justify-content-center">
                            <div class="d-flex align-items-center justify-content-center gap-2 text-center flex-column p-4 w-75"
                                style="gap: 1rem;">
                                <img src="{{ asset('images/sl1.png') }}" class="w-100 h-50 object-fit-cover"
                                    alt="Slide 3">
                                <div>
                                    <h3>Team Management with WorkNex</h3>
                                    <p>
                                        Businesses can streamline operations using our all-in-one WorkNex 
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Navigation + Pagination -->
                    {{-- <div class="swiper-pagination"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div> --}}
                </div>
            </div>

        </div>

    </section>

    <!-- Font Awesome -->
    <script src="{{ asset('vendor/jquery/all.min.js') }}" defer="defer"></script>

    <!-- Template JS -->
    <script src="{{ asset('js/main.js') }}"></script>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
        document.loading = '@lang('app.loading')';
        const MODAL_DEFAULT = '#myModalDefault';
        const MODAL_LG = '#myModal';
        const MODAL_XL = '#myModalXl';
        const MODAL_HEADING = '#modelHeading';
        const RIGHT_MODAL = '#task-detail-1';
        const RIGHT_MODAL_CONTENT = '#right-modal-content';
        const RIGHT_MODAL_TITLE = '#right-modal-title';

        const dropifyMessages = {
            default: "@lang('app.dragDrop')",
            replace: "@lang('app.dragDropReplace')",
            remove: "@lang('app.remove')",
            error: "@lang('messages.errorOccured')",
        };
        $('.change-lang').click(function(event) {
            const locale = $(this).data("lang");
            event.preventDefault();
            let url = "{{ route('front.changeLang', ':locale') }}";
            url = url.replace(':locale', locale);
            $.easyAjax({
                url: url,
                container: '#login-form',
                blockUI: true,
                type: "GET",
                success: function(response) {
                    if (response.status === 'success') {
                        window.location.reload();
                    }
                }
            })
        });
    </script>

    <script>
        var swiper = new Swiper(".mySwiper", {
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            grabCursor: true, // mouse grab effect
        });

        function setContainerScale(containerSelector) {
            const container = document.querySelector(containerSelector);
            if (!container) return;

            const baseWidth = 320; // reference width
            const baseScale = 0.33; // scale at 320px
            const maxWidth = 1024; // maximum width for scaling

            // Calculate scale based on current viewport width
            const screenWidth = window.innerWidth;
            const scale =
                screenWidth >= maxWidth ?
                1 // no scaling above 1024px
                :
                baseScale + ((screenWidth - baseWidth) / (maxWidth - baseWidth)) * (1 - baseScale);

            container.style.transform = `scale(${scale})`;
            container.style.transformOrigin = "center";
        }

        // Run on load and resize
        window.addEventListener("load", () => setContainerScale(".login_wrapper"));
        window.addEventListener("resize", () => setContainerScale(".login_wrapper"));
    </script>


    {{ $scripts }}



</body>

</html>
