@extends(getTemplate() . '.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')
    @php
        $registerMethod = getGeneralSettings('register_method') ?? 'mobile';
        $showOtherRegisterMethod = getFeaturesSettings('show_other_register_method') ?? false;
        $showCertificateAdditionalInRegister = getFeaturesSettings('show_certificate_additional_in_register') ?? false;
        $selectRolesDuringRegistration = getFeaturesSettings('select_the_role_during_registration') ?? null;
    @endphp

    <div class="container">
        <div class="row login-container justify-content-center">

            <div class="col-12 col-md-6 col-lg-6">
                <div class="login-card">
                    <h1 class="font-20 font-weight-bold">{{ trans('auth.signup') }}</h1>

                    <form method="post" action="/register" class="mt-35">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="redirect_to" value="{{ request()->get('redirect_to', '') }}">


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

                        <!-- Password Field with Show/Hide -->
                        <div class="form-group">
                            <label class="input-label" for="password">{{ trans('auth.password') }}:</label>
                            <div class="input-group position-relative">
                                <input name="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" id="password">
                                <i class="bx bx-hide position-absolute toggle-password" data-target="password"
                                    style="right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 1.2rem; z-index: 3;"></i>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password Field with Show/Hide -->
                        <div class="form-group">
                            <label class="input-label" for="confirm_password">{{ trans('auth.retype_password') }}:</label>
                            <div class="input-group position-relative">
                                <input name="password_confirmation" type="password"
                                    class="form-control @error('password_confirmation') is-invalid @enderror"
                                    id="confirm_password">
                                <i class="bx bx-hide position-absolute toggle-password" data-target="confirm_password"
                                    style="right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 1.2rem; z-index: 3;"></i>
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
                        <a href="/google" 
                            class="social-login mt-20 p-10 text-center d-flex align-items-center justify-content-center">
                            <img src="/assets/default/img/auth/google.svg" class="mr-auto" alt=" google svg" />
                            <span class="flex-grow-1">Continue with Google</span>
                        </a>
                    @endif


                    <div class="text-center mt-20">
                        <span class="text-secondary">
                            {{ trans('auth.already_have_an_account') }}
                            <a href="#" class="text-secondary font-weight-bold" data-toggle="modal"
                                data-target="#loginModal">{{ trans('auth.login') }}</a>
                        </span>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="signupModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="signupModalLabel">{{ trans('auth.signup') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form method="Post" action="/login" class="mt-35">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        @include('web.default.auth.includes.register_methods')

                        <!-- Password Field with Show/Hide -->
                        <div class="form-group">
                            <label class="input-label" for="password">{{ trans('auth.password') }}:</label>
                            <div class="input-group position-relative">
                                <input name="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" id="password">
                                <i class="bx bx-hide position-absolute toggle-password" data-target="password"
                                    style="right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 1.2rem;"></i>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- CAPTCHA for Login -->
                        @if (!empty(getGeneralSecuritySettings('captcha_for_login')))
                            @include('web.default.includes.captcha_input')
                        @endif

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary btn-block mt-20">{{ trans('auth.login') }}</button>

                        <!-- Social Login Options -->
                        <div class="text-center mt-20">
                            <span
                                class="badge badge-circle-gray300 text-secondary d-inline-flex align-items-center justify-content-center">{{ trans('auth.or') }}</span>
                        </div>

                        @if (!empty(getFeaturesSettings('show_google_login_button')))
                            <a href="/google" 
                                class="social-login mt-20 p-10 text-center d-flex align-items-center justify-content-center">
                                <img src="/assets/default/img/auth/google.svg" class="mr-auto" alt="Google Login" />
                                <span class="flex-grow-1">Continue with Google</span>
                            </a>
                        @endif

                        @if (!empty(getFeaturesSettings('show_facebook_login_button')))
                            <a href="{{ url('/facebook/redirect') }}" target="_blank"
                                class="social-login mt-20 p-10 text-center d-flex align-items-center justify-content-center">
                                <img src="/assets/default/img/auth/facebook.svg" class="mr-auto" alt="Facebook Login" />
                                <span class="flex-grow-1">{{ trans('auth.facebook_login') }}</span>
                            </a>
                        @endif

                        <!-- Forgot Password -->
                        <div class="mt-30 text-center">
                            <a href="/forget-password" target="_blank">{{ trans('auth.forget_your_password') }}</a>
                        </div>
                    </form>

                    <script>
                        document.querySelectorAll('.toggle-password').forEach(function(icon) {
                            icon.addEventListener('click', function() {
                                const targetId = this.getAttribute('data-target');
                                const inputField = document.getElementById(targetId);
                                const isPassword = inputField.type === 'password';
                                inputField.type = isPassword ? 'text' : 'password';
                                this.classList.toggle('bx-show');
                                this.classList.toggle('bx-hide');
                            });
                        });
                    </script>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script src="/assets/default/js/parts/forms.min.js"></script>
    <script src="/assets/default/js/parts/register.min.js"></script>

    <script>
        document.querySelectorAll('.toggle-password').forEach(function(icon) {
            icon.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const inputField = document.getElementById(targetId);
                const isPassword = inputField.type === 'password';
                inputField.type = isPassword ? 'text' : 'password';
                this.classList.toggle('bx-show');
                this.classList.toggle('bx-hide');
            });
        });
    </script>
@endpush
