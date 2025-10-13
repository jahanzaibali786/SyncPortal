@push('styles')
    @foreach ($frontWidgets as $item)
    @if(!is_null($item->header_script))
        {!! $item->header_script !!}
    @endif
    @endforeach

    <style>
        .auto-fill-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 20px;
        }
        .auto-fill-grid.three-buttons {
            grid-template-columns: 1fr 1fr 1fr;
        }
        .auto-fill-btn {
            padding: 10px 15px;
            border-radius: 20px;
            cursor: pointer;
            text-align: center;
            border: 1px solid var(--primary-color, #1d82f5);
            color: var(--primary-color, #1d82f5);
            background-color: transparent;
            transition: all 0.3s ease;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 13px;
        }
        .auto-fill-btn:hover {
            background-color: var(--primary-color, #1d82f5);
            color: white;
        }
        /* Hide forgot password and signup buttons */
        .forgot_pswd, #signup-client-next, #signup-customer {
            display: none !important;
        }
    </style>
@endpush

<x-auth>

    <form id="login-form" action="{{ route('login') }}" class="ajax-form" method="POST">
        {{ csrf_field() }}
        <h3 class=" mb-4 f-w-500">@lang('app.login')</h3>

        <script>
            const facebook = "{{ route('social_login', 'facebook') }}";
            const google = "{{ route('social_login', 'google') }}";
            const twitter = "{{ route('social_login', 'twitter-oauth-2') }}";
            const linkedin = "{{ route('social_login', 'linkedin-openid') }}";
        </script>

        @if ($socialAuthSettings->google_status == 'enable')
            <a class="mb-3 height_50 rounded f-w-500" onclick="window.location.href = google;">
                <span><img src="{{ asset('img/google.png') }}" alt="Google"/></span>
                @lang('auth.signInGoogle')</a>
        @endif
        @if ($socialAuthSettings->facebook_status == 'enable')
            <a class="mb-3 height_50 rounded f-w-500" onclick="window.location.href = facebook;">
                <span><img src="{{ asset('img/fb.png') }}" alt="Google"/></span>
                @lang('auth.signInFacebook')
            </a>
        @endif
        @if ($socialAuthSettings->twitter_status == 'enable')
            <a class="mb-3 height_50 rounded f-w-500" onclick="window.location.href = twitter;">
                <span><img src="{{ asset('img/twitter.png') }}" alt="Google"/></span>
                @lang('auth.signInTwitter')
            </a>
        @endif
        @if ($socialAuthSettings->linkedin_status == 'enable')
            <a class="mb-3 height_50 rounded f-w-500" onclick="window.location.href = linkedin;">
                <span><img src="{{ asset('img/linkedin.png') }}" alt="Google"/></span>
                @lang('auth.signInLinkedin')
            </a>
        @endif

        @if ($socialAuthSettings->social_auth_enable)
            <p class="position-relative my-4">@lang('auth.useEmail')</p>
        @endif

        <div class="form-group text-left">
            <label for="email">@lang('auth.email')</label>
            <input tabindex="1" type="email" name="email"
                   class="form-control height-50 f-15 light_text @error('email') is-invalid @enderror"
                   autofocus
                   value="{{request()->old('email')}}"
                   placeholder="@lang('auth.email')" id="email">
            @if ($errors->has('email'))
                <div class="invalid-feedback">{{ $errors->first('email') }}</div>
            @endif
        </div>

        <div id="password-section">
            <div class="form-group text-left">
                <label for="password">@lang('app.password')</label>
                <x-forms.input-group>
                    <input type="password" name="password" id="password"
                           placeholder="@lang('placeholders.password')" tabindex="3"
                           class="form-control height-50 f-15 light_text @error('password') is-invalid @enderror">

                    <x-slot name="append">
                        <button type="button" data-toggle="tooltip"
                                data-original-title="@lang('app.viewPassword')"
                                class="btn btn-outline-secondary border-grey height-50 toggle-password">
                            <i
                                class="fa fa-eye"></i></button>
                    </x-slot>

                </x-forms.input-group>
                @if ($errors->has('password'))
                    <div class="invalid-feedback d-block">{{ $errors->first('password') }}</div>
                @endif
            </div>

            <div class="form-group text-left ">
                <input id="checkbox-signup" class="cursor-pointer" type="checkbox" name="remember">
                <label for="checkbox-signup" class="cursor-pointer">@lang('app.rememberMe')</label>
            </div>

            @if ($globalSetting->google_recaptcha_status == 'active')
                <div class="form-group" id="captcha_container"></div>
            @endif

            <input type="hidden" id="g_recaptcha" name="g_recaptcha">

            @if ($errors->has('g-recaptcha-response'))
                <div
                    class="invalid-feedback  d-block text-left">{{ $errors->first('g-recaptcha-response') }}
                </div>
            @endif

            <button type="submit" id="submit-login"
                    class="btn-primary f-w-500 rounded w-100 height-50 f-18">
                @lang('app.login') <i class="fa fa-arrow-right pl-1"></i>
            </button>
            
            <div class="auto-fill-grid three-buttons">
                <button type="button" class="auto-fill-btn" id="admin-login">Admin</button>
                <button type="button" class="auto-fill-btn" id="employee-login">Employee</button>
                <button type="button" class="auto-fill-btn" id="client-login">Client</button>
            </div>
        </div>

        <input type="hidden" id="current-latitude" name="current_latitude">
        <input type="hidden" id="current-longitude" name="current_longitude">
    </form>
    </form>

    <x-slot name="scripts">
        <script>
            @if (isWorksuite() && ($company->attendance_status == 'active' && ($company->attendance_setting->radius_check == 'yes' || $company->attendance_setting->save_current_location == 'yes') ))
                function setCurrentLocation() {
                    const currentLatitude = document.getElementById("current-latitude");
                    const currentLongitude = document.getElementById("current-longitude");

                    function getLocation() {
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(showPosition);
                        }
                    }

                    function showPosition(position) {
                        currentLatitude.value = position.coords.latitude;
                        currentLongitude.value = position.coords.longitude;
                    }
                    getLocation();
                }
                setCurrentLocation();
            @endif
        </script>

        @if ($globalSetting->google_recaptcha_status == 'active' && $globalSetting->google_recaptcha_v2_status == 'active')
            <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async
                    defer></script>
            <script>
                var gcv3;
                var onloadCallback = function () {
                    // Renders the HTML element with id 'captcha_container' as a reCAPTCHA widget.
                    // The id of the reCAPTCHA widget is assigned to 'gcv3'.
                    gcv3 = grecaptcha.render('captcha_container', {
                        'sitekey': '{{ $globalSetting->google_recaptcha_v2_site_key }}',
                        'theme': 'light',
                        'callback': function (response) {
                            if (response) {
                                $('#g_recaptcha').val(response);
                            }
                        },
                    });
                };
            </script>
        @endif
        @if ($globalSetting->google_recaptcha_status == 'active' && $globalSetting->google_recaptcha_v3_status == 'active')
            <script
                src="https://www.google.com/recaptcha/api.js?render={{ $globalSetting->google_recaptcha_v3_site_key }}"></script>
            <script>
                grecaptcha.ready(function () {
                    grecaptcha.execute('{{ $globalSetting->google_recaptcha_v3_site_key }}').then(function (token) {
                        // Add your logic to submit to your backend server here.
                        $('#g_recaptcha').val(token);
                    });
                });
            </script>
        @endif

        <script>
            $(document).ready(function () {
                // Auto-fill buttons functionality
                $('#admin-login').click(function() {
                    $('#email').val('admin@example.com');
                    $('#password').val('12345678');
                    setTimeout(function() {
                        $('#submit-login').click();
                    }, 100);
                });
                
                $('#employee-login').click(function() {
                    $('#email').val('employee@syncsuite.com');
                    $('#password').val('12345678');
                    setTimeout(function() {
                        $('#submit-login').click();
                    }, 100);
                });
                
                $('#client-login').click(function() {
                    $('#email').val('client@syncsuite.com');
                    $('#password').val('12345678');
                    setTimeout(function() {
                        $('#submit-login').click();
                    }, 100);
                });

                $("form#login-form").submit(function () {
                    const button = $('form#login-form').find('#submit-login');
                    const text = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{__('app.loading')}}';
                    button.prop("disabled", true);
                    button.html(text);
                });

                @if (session('message'))
                Swal.fire({
                    icon: 'error',
                    text: '{{ session('message') }}',
                    showConfirmButton: true,
                    customClass: {
                        confirmButton: 'btn btn-primary',
                    },
                    showClass: {
                        popup: 'swal2-noanimation',
                        backdrop: 'swal2-noanimation'
                    },
                })
                @endif
            });
        </script>

        @foreach ($frontWidgets as $item)
        @if(!is_null($item->footer_script))
            {!! $item->footer_script !!}
        @endif
        @endforeach
    </x-slot>
</x-auth>