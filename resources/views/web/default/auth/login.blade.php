@extends(getTemplate() . '.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
@endpush

@section('content')
    <div class="container">
        @if (!empty(session()->has('msg')))
            <div class="alert alert-info alert-dismissible fade show mt-30" role="alert">
                {{ session()->get('msg') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row login-container justify-content-center">


            <div class="col-12 col-md-6 col-lg-6">
                <div class="login-card" id="loginForm">
                    <!-- Toggle between Login and Signup -->
                    <div class="toggle-container">
                        <div class="toggle-buttons">
                            <button id="toggleLogin" class="toggle-btn active">Login</button>
                            <button id="toggleSignup" class="toggle-btn">Signup</button>
                        </div>
                    </div>

                    <h1 class="font-20 font-weight-bold">{{ trans('auth.login_h1') }}</h1>

                    <form method="POST" action="/login" class="mt-35">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="redirect_to" value="{{ request()->get('redirect_to', '/') }}">

                        @include('web.default.auth.includes.register_methods')

                        <div class="form-group">
                            <label class="input-label" for="password">{{ trans('auth.password') }}:</label>
                            <div class="input-group position-relative">
                                <input name="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" id="password"
                                    aria-describedby="passwordHelp">
                                <i class="bx bx-hide position-absolute" id="togglePassword"
                                    style="right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 1.2rem; z-index:3;"></i>
                            </div>

                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        @if (!empty(getGeneralSecuritySettings('captcha_for_login')))
                            @include('web.default.includes.captcha_input')
                        @endif

                        <button type="submit" class="btn btn-primary btn-block mt-20">{{ trans('auth.login') }}</button>
                    </form>

                    <script>
                        document.getElementById('togglePassword').addEventListener('click', function() {
                            const passwordField = document.getElementById('password');
                            const isPassword = passwordField.type === 'password';
                            passwordField.type = isPassword ? 'text' : 'password';
                            this.classList.toggle('bx-show');
                            this.classList.toggle('bx-hide');
                        });
                    </script>


                    @if (session()->has('login_failed_active_session'))
                        <div class="d-flex align-items-center mt-20 p-15 danger-transparent-alert ">
                            <div class="danger-transparent-alert__icon d-flex align-items-center justify-content-center">
                                <i data-feather="alert-octagon" width="18" height="18" class=""></i>
                            </div>
                            <div class="ml-10">
                                <div class="font-14 font-weight-bold ">
                                    {{ session()->get('login_failed_active_session')['title'] }}</div>
                                <div class="font-12 ">{{ session()->get('login_failed_active_session')['msg'] }}</div>
                            </div>
                        </div>
                    @endif

                    <div class="text-center mt-20">
                        <span
                            class="badge badge-circle-gray300 text-secondary d-inline-flex align-items-center justify-content-center">{{ trans('auth.or') }}</span>
                    </div>

                    {{-- new updates --}}
                    @if (!empty(getFeaturesSettings('show_google_login_button')))
                        <a href="{{ url('/google?redirect_to=' . urlencode(session('redirect_to', url()->current()))) }}"
                           
                            class="social-login mt-20 p-10 text-center d-flex align-items-center justify-content-center">
                            <img src="/assets/default/img/auth/google.svg" class="mr-auto" alt="google svg" />
                            <span class="flex-grow-1">Continue with Google</span>
                        </a>
                    @endif

                    @if (!empty(getFeaturesSettings('show_facebook_login_button')))
                        <a href="{{ url('/facebook/redirect') }}" target="_blank"
                            class="social-login mt-20 p-10 text-center d-flex align-items-center justify-content-center ">
                            <img src="/assets/default/img/auth/facebook.svg" class="mr-auto" alt="facebook svg" />
                            <span class="flex-grow-1">{{ trans('auth.facebook_login') }}</span>
                        </a>
                    @endif

                    <div class="mt-30 text-center">
                        <a href="/forget-password" target="_blank">{{ trans('auth.forget_your_password') }}</a>
                    </div>

                    <div class="mt-20 text-center">
                        <span>{{ trans('auth.dont_have_account') }}</span>
                        <a href="#" class="text-secondary font-weight-bold" data-toggle="modal"
                            data-target="#signupModal">{{ trans('auth.signup') }}</a>
                    </div>

                </div>

                <!-- Signup Form -->
                <div id="signupForm" style="display: none;">
                    <div class="toggle-container">
                        <div class="text-center mb-4 mt-4 toggle-buttons">
                            <button id="backToLogin" type="button" class="toggle-btn">Login</button>
                            <button id="backToLogin" type="button" class="toggle-btn active">Signup</button>
                        </div>

                    </div>

                    <h1 class="font-20 font-weight-bold">{{ trans('auth.signup') }}</h1>

                    <form method="post" action="/register" class="mt-35">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <!-- Hidden field for redirect_to will be populated by JavaScript -->
                        <input type="hidden" name="redirect_to" value="{{ session('intended_url', url()->current()) }}">



                        @if (!empty($selectRolesDuringRegistration) and count($selectRolesDuringRegistration))
                            @php
                                $oldAccountType = old('account_type');
                            @endphp

                            <div class="form-group">
                                <label class="input-label">{{ trans('financial.account_type') }}</label>

                                <div class="d-flex align-items-center wizard-custom-radio mt-5">
                                    <div class="wizard-custom-radio-item flex-grow-1">
                                        <input type="radio" name="account_type" value="user" id="role_user"
                                            class=""
                                            {{ (empty($oldAccountType) or $oldAccountType == 'user') ? 'checked' : '' }}>
                                        <label class="font-12 cursor-pointer px-15 py-10"
                                            for="role_user">{{ trans('update.role_user') }}</label>
                                    </div>

                                    @foreach ($selectRolesDuringRegistration as $selectRole)
                                        <div class="wizard-custom-radio-item flex-grow-1">
                                            <input type="radio" name="account_type" value="{{ $selectRole }}"
                                                id="role_{{ $selectRole }}" class=""
                                                {{ $oldAccountType == $selectRole ? 'checked' : '' }}>
                                            <label class="font-12 cursor-pointer px-15 py-10"
                                                for="role_{{ $selectRole }}">{{ trans('update.role_' . $selectRole) }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($registerMethod == 'mobile')
                            @include('web.default.auth.register_includes.mobile_field')

                            @if ($showOtherRegisterMethod)
                                @include('web.default.auth.register_includes.email_field', [
                                    'optional' => true,
                                ])
                            @endif
                        @else
                            @include('web.default.auth.register_includes.email_field')

                            @if ($showOtherRegisterMethod)
                                @include('web.default.auth.register_includes.mobile_field', [
                                    'optional' => true,
                                ])
                            @endif
                        @endif

                        <div class="form-group">
                            <label class="input-label" for="full_name">{{ trans('auth.full_name') }}:</label>
                            <input name="full_name" type="text" value="{{ old('full_name') }}"
                                class="form-control @error('full_name') is-invalid @enderror">
                            @error('full_name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="input-label" for="password">{{ trans('auth.password') }}:</label>
                            <div class="input-group position-relative">
                                <input name="password" type="password" class="form-control @error('password') is-invalid @enderror" id="password" aria-describedby="passwordHelp">
                                <i class="bx bx-hide position-absolute toggle-password" data-target="password" style="right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 1.2rem; z-index: 3;"></i>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label class="input-label" for="confirm_password">{{ trans('auth.retype_password') }}:</label>
                            <div class="input-group position-relative">
                                <input name="password_confirmation" type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="confirm_password" aria-describedby="confirmPasswordHelp">
                                <i class="bx bx-hide position-absolute toggle-password" data-target="confirm_password" style="right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 1.2rem; z-index: 3;"></i>
                            </div>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @if ($showCertificateAdditionalInRegister)
                            <div class="form-group">
                                <label class="input-label"
                                    for="certificate_additional">{{ trans('update.certificate_additional') }}</label>
                                <input name="certificate_additional" id="certificate_additional"
                                    class="form-control @error('certificate_additional') is-invalid @enderror" />
                                @error('certificate_additional')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        @endif

                        @if (getFeaturesSettings('timezone_in_register'))
                            @php
                                $selectedTimezone = getGeneralSettings('default_time_zone');
                            @endphp

                            <div class="form-group">
                                <label class="input-label">{{ trans('update.timezone') }}</label>
                                <select name="timezone" class="form-control select2" data-allow-clear="false">
                                    <option value="" {{ empty($user->timezone) ? 'selected' : '' }} disabled>
                                        {{ trans('public.select') }}</option>
                                    @foreach (getListOfTimezones() as $timezone)
                                        <option value="{{ $timezone }}"
                                            @if ($selectedTimezone == $timezone) selected @endif>{{ $timezone }}</option>
                                    @endforeach
                                </select>
                                @error('timezone')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        @endif

                        @if (!empty($referralSettings) and $referralSettings['status'])
                            <div class="form-group ">
                                <label class="input-label"
                                    for="referral_code">{{ trans('financial.referral_code') }}:</label>
                                <input name="referral_code" type="text"
                                    class="form-control @error('referral_code') is-invalid @enderror" id="referral_code"
                                    value="{{ !empty($referralCode) ? $referralCode : old('referral_code') }}"
                                    aria-describedby="confirmPasswordHelp">
                                @error('referral_code')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        @endif

                        <div class="js-form-fields-card">
                            @if (!empty($formFields))
                                {!! $formFields !!}
                            @endif
                        </div>

                        @if (!empty(getGeneralSecuritySettings('captcha_for_register')))
                            @include('web.default.includes.captcha_input')
                        @endif

                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="term" value="1"
                                {{ (!empty(old('term')) and old('term') == '1') ? 'checked' : '' }}
                                class="custom-control-input @error('term') is-invalid @enderror" id="term">
                            <label class="custom-control-label font-14" for="term">{{ trans('auth.i_agree_with') }}
                                <a href="pages/terms" target="_blank"
                                    class="text-secondary font-weight-bold font-14">{{ trans('auth.terms_and_rules') }}</a>
                            </label>

                            @error('term')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        @error('term')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        <button type="submit"
                            class="btn btn-primary btn-block mt-20">{{ trans('auth.signup') }}</button>
                    </form>


                    <div class="text-center mt-20">
                        <span
                            class="badge badge-circle-gray300 text-secondary d-inline-flex align-items-center justify-content-center">{{ trans('auth.or') }}</span>
                    </div>

                    @if (!empty(getFeaturesSettings('show_google_login_button')))
                        <a href="{{ url('/google?redirect_to=' . urlencode(session('redirect_to', url()->current()))) }}"
                            class="social-login mt-20 mb-4 p-10 text-center d-flex align-items-center justify-content-center">
                            <img src="/assets/default/img/auth/google.svg" class="mr-auto" alt="google svg" />
                            <span class="flex-grow-1">Continue with Google </span>
                        </a>
                    @endif





                </div>
                <!-- Pop up Modal -->
                <!-- Pop up Modal -->
                <!-- Pop up Modal -->
                <div class="modal fade" id="signupModal" tabindex="-1" role="dialog"
                    aria-labelledby="signupModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="signupModalLabel">{{ trans('auth.signup') }}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">


                                <form method="post" action="/register" class="mt-35">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="redirect_to"
                                        value="{{ request()->get('redirect_to', '') }}">


                                    @if (!empty($selectRolesDuringRegistration) and count($selectRolesDuringRegistration))
                                        @php
                                            $oldAccountType = old('account_type');
                                        @endphp

                                        <div class="form-group">
                                            <label class="input-label">{{ trans('financial.account_type') }}</label>

                                            <div class="d-flex align-items-center wizard-custom-radio mt-5">
                                                <div class="wizard-custom-radio-item flex-grow-1">
                                                    <input type="radio" name="account_type" value="user"
                                                        id="role_user" class=""
                                                        {{ (empty($oldAccountType) or $oldAccountType == 'user') ? 'checked' : '' }}>
                                                    <label class="font-12 cursor-pointer px-15 py-10"
                                                        for="role_user">{{ trans('update.role_user') }}</label>
                                                </div>

                                                @foreach ($selectRolesDuringRegistration as $selectRole)
                                                    <div class="wizard-custom-radio-item flex-grow-1">
                                                        <input type="radio" name="account_type"
                                                            value="{{ $selectRole }}" id="role_{{ $selectRole }}"
                                                            class=""
                                                            {{ $oldAccountType == $selectRole ? 'checked' : '' }}>
                                                        <label class="font-12 cursor-pointer px-15 py-10"
                                                            for="role_{{ $selectRole }}">{{ trans('update.role_' . $selectRole) }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if ($registerMethod == 'mobile')
                                        @include('web.default.auth.register_includes.mobile_field')

                                        @if ($showOtherRegisterMethod)
                                            @include('web.default.auth.register_includes.email_field', [
                                                'optional' => true,
                                            ])
                                        @endif
                                    @else
                                        @include('web.default.auth.register_includes.email_field')

                                        @if ($showOtherRegisterMethod)
                                            @include('web.default.auth.register_includes.mobile_field', [
                                                'optional' => true,
                                            ])
                                        @endif
                                    @endif

                                    <div class="form-group">
                                        <label class="input-label" for="full_name">{{ trans('auth.full_name') }}:</label>
                                        <input name="full_name" type="text" value="{{ old('full_name') }}"
                                            class="form-control @error('full_name') is-invalid @enderror">
                                        @error('full_name')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="input-label" for="password">{{ trans('auth.password') }}:</label>
                                        <input name="password" type="password"
                                            class="form-control @error('password') is-invalid @enderror" id="password"
                                            aria-describedby="passwordHelp">
                                        @error('password')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group ">
                                        <label class="input-label"
                                            for="confirm_password">{{ trans('auth.retype_password') }}:</label>
                                        <input name="password_confirmation" type="password"
                                            class="form-control @error('password_confirmation') is-invalid @enderror"
                                            id="confirm_password" aria-describedby="confirmPasswordHelp">
                                        @error('password_confirmation')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    @if ($showCertificateAdditionalInRegister)
                                        <div class="form-group">
                                            <label class="input-label"
                                                for="certificate_additional">{{ trans('update.certificate_additional') }}</label>
                                            <input name="certificate_additional" id="certificate_additional"
                                                class="form-control @error('certificate_additional') is-invalid @enderror" />
                                            @error('certificate_additional')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    @endif

                                    @if (getFeaturesSettings('timezone_in_register'))
                                        @php
                                            $selectedTimezone = getGeneralSettings('default_time_zone');
                                        @endphp

                                        <div class="form-group">
                                            <label class="input-label">{{ trans('update.timezone') }}</label>
                                            <select name="timezone" class="form-control select2"
                                                data-allow-clear="false">
                                                <option value="" {{ empty($user->timezone) ? 'selected' : '' }}
                                                    disabled>{{ trans('public.select') }}</option>
                                                @foreach (getListOfTimezones() as $timezone)
                                                    <option value="{{ $timezone }}"
                                                        @if ($selectedTimezone == $timezone) selected @endif>
                                                        {{ $timezone }}</option>
                                                @endforeach
                                            </select>
                                            @error('timezone')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    @endif

                                    @if (!empty($referralSettings) and $referralSettings['status'])
                                        <div class="form-group ">
                                            <label class="input-label"
                                                for="referral_code">{{ trans('financial.referral_code') }}:</label>
                                            <input name="referral_code" type="text"
                                                class="form-control @error('referral_code') is-invalid @enderror"
                                                id="referral_code"
                                                value="{{ !empty($referralCode) ? $referralCode : old('referral_code') }}"
                                                aria-describedby="confirmPasswordHelp">
                                            @error('referral_code')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    @endif

                                    <div class="js-form-fields-card">
                                        @if (!empty($formFields))
                                            {!! $formFields !!}
                                        @endif
                                    </div>

                                    @if (!empty(getGeneralSecuritySettings('captcha_for_register')))
                                        @include('web.default.includes.captcha_input')
                                    @endif

                                    <!-- Custom Checkbox -->
                                    <div class="form-group form-check mt-3">
                                        <input type="checkbox"
                                            class="form-check-input @error('term') is-invalid @enderror" id="term"
                                            name="term" value="1" {{ old('term') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="term">
                                            {{ trans('auth.i_agree_with') }}
                                            <a href="pages/terms" target="_blank" class="text-primary font-weight-bold">
                                                {{ trans('auth.terms_and_rules') }}
                                            </a>
                                        </label>
                                        @error('term')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>


                                    @error('term')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                    <button type="submit"
                                        class="btn btn-primary btn-block mt-20">{{ trans('auth.signup') }}</button>
                                </form>

                                <div class="text-center mt-20">
                                    <span
                                        class="badge badge-circle-gray300 text-secondary d-inline-flex align-items-center justify-content-center">{{ trans('auth.or') }}</span>
                                </div>

                                @if (!empty(getFeaturesSettings('show_google_login_button')))
                                    <a href="/google" 
                                        class="social-login mt-20 p-10 text-center d-flex align-items-center justify-content-center">
                                        <img src="/assets/default/img/auth/google.svg" class="mr-auto"
                                            alt=" google svg" />
                                        <span class="flex-grow-1">Continue with Google</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>







            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script src="/assets/default/js/parts/forgot_password.min.js"></script>

    <script>
        document.getElementById('toggleLogin').addEventListener('click', function() {
            // Show/hide forms
            document.getElementById('loginForm').style.display = 'block';
            document.getElementById('signupForm').style.display = 'none';

            // Toggle button styles
            this.classList.add('active');
            this.classList.remove('inactive');
            document.getElementById('toggleSignup').classList.add('inactive');
            document.getElementById('toggleSignup').classList.remove('active');
        });

        document.getElementById('toggleSignup').addEventListener('click', function() {
            // Show/hide forms
            document.getElementById('signupForm').style.display = 'block';
            document.getElementById('loginForm').style.display = 'none';

            // Toggle button styles
            this.classList.add('active');
            this.classList.remove('inactive');
            document.getElementById('toggleLogin').classList.add('inactive');
            document.getElementById('toggleLogin').classList.remove('active');
        });

        // Add event listener for "Back to Login" button
        document.getElementById('backToLogin').addEventListener('click', function() {
            // Show/hide forms
            document.getElementById('signupForm').style.display = 'none';
            document.getElementById('loginForm').style.display = 'block';

            // Toggle button styles
            document.getElementById('toggleLogin').classList.add('active');
            document.getElementById('toggleLogin').classList.remove('inactive');
            document.getElementById('toggleSignup').classList.add('inactive');
            document.getElementById('toggleSignup').classList.remove('active');
        });
    </script>
<script>
    document.querySelectorAll('.toggle-password').forEach(function(icon) {
        icon.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const inputField = document.getElementById(targetId);
            const isPassword = inputField.type === 'password';
            inputField.type = isPassword ? 'text' : 'password';
            
            // Toggle icons for both password and confirm password fields
            if (inputField.type === 'password') {
                this.classList.replace('bx-show', 'bx-hide');
            } else {
                this.classList.replace('bx-hide', 'bx-show');
            }
        });
    });
</script>
@endpush
