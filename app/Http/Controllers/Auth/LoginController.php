<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\CartManagerController;
use App\Mixins\Logs\UserLoginHistoryMixin;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\UserSession;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/panel';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {

        $seoSettings = getSeoMetas('login');
        $pageTitle = !empty($seoSettings['title']) ? $seoSettings['title'] : trans('site.login_page_title');
        $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('site.login_page_title');
        $pageRobot = getPageRobot('login');


        //new updates
        $registerMethod = getGeneralSettings('register_method') ?? 'mobile'; // Set your method here
        $showOtherRegisterMethod = false; // Set this variable as needed (true or false)
        $showCertificateAdditionalInRegister = false; // Set this as needed (true or false)





        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'registerMethod' => $registerMethod, // Include registerMethod here
            'showOtherRegisterMethod' => $showOtherRegisterMethod, // Include this variable
            'showCertificateAdditionalInRegister' => $showCertificateAdditionalInRegister, // Include this variable



        ];

        return view(getTemplate() . '.auth.login', $data);
    }
    //new updates
    public function login(Request $request)
    {
        $type = $request->get('type');
        $redirectTo = $request->input('redirect_to', '/'); // Default to homepage if no redirect_to is provided

          // Store redirect URL in session if it's passed as a query parameter
    if ($request->has('redirect_to')) {
        session(['intended_url' => $redirectTo]);
    }

        // Define validation rules based on login type
        $rules = $type == 'mobile' ? [
            'mobile' => 'required|numeric',
            'country_code' => 'required',
            'password' => 'required|min:6',
        ] : [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6',
        ];

        if (!empty(getGeneralSecuritySettings('captcha_for_login'))) {
            $rules['captcha'] = 'required|captcha';
        }

        $this->validate($request, $rules, [], [
            'mobile' => trans('auth.mobile'),
            'email' => trans('auth.email'),
            'captcha' => trans('site.captcha'),
            'password' => trans('auth.password'),
        ]);

        // Additional validation for mobile login
        if ($type == 'mobile') {
            $value = $this->getUsernameValue($request);
            if (!checkMobileNumber("+{$value}")) {
                return back()->withErrors(['mobile' => trans('update.mobile_number_is_not_valid')])->withInput($request->all());
            }
        }

        // Attempt to log in the user
        if ($this->attemptLogin($request)) {
            // Redirect after successful login, using the $redirectTo variable
            return $this->afterLogged($request, false, $redirectTo);
        }

        return $this->sendFailedLoginResponse($request);
    }



    public function logout(Request $request)
    {
        $user = auth()->user();

        $userLoginHistoryMixin = new UserLoginHistoryMixin();
        $userLoginHistoryMixin->storeUserLogoutHistory($user->id);

        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if (!empty($user) and $user->logged_count > 0) {

            $user->update([
                'logged_count' => $user->logged_count - 1
            ]);
        }

        return redirect('/');
    }

    public function username()
    {
        $email_regex = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

        if (empty($this->username)) {
            $this->username = 'mobile';
            if (preg_match($email_regex, request('username', null))) {
                $this->username = 'email';
            }
        }
        return $this->username;
    }

    protected function getUsername(Request $request)
    {
        $type = $request->get('type');

        if ($type == 'mobile') {
            return 'mobile';
        } else {
            return 'email';
        }
    }

    protected function getUsernameValue(Request $request)
    {
        $type = $request->get('type');
        $data = $request->all();

        if ($type == 'mobile') {
            return ltrim($data['country_code'], '+') . ltrim($data['mobile'], '0');
        } else {
            return $request->get('email');
        }
    }

    protected function attemptLogin(Request $request)
    {
        $credentials = [
            $this->getUsername($request) => $this->getUsernameValue($request),
            'password' => $request->get('password')
        ];
        $remember = true;

        /*if (!empty($request->get('remember')) and $request->get('remember') == true) {
            $remember = true;
        }*/

        return $this->guard()->attempt($credentials, $remember);
    }

    public function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->getUsername($request) => [trans('validation.password_or_username')],
        ]);
    }

    protected function sendBanResponse(Request $request, $user)
    {
        throw ValidationException::withMessages([
            $this->getUsername($request) => [trans('auth.ban_msg', ['date' => dateTimeFormat($user->ban_end_at, 'j M Y')])],
        ]);
    }

    protected function sendNotActiveResponse($user)
    {
        $toastData = [
            'title' => trans('public.request_failed'),
            'msg' => trans('auth.login_failed_your_account_is_not_verified'),
            'status' => 'error'
        ];

        return redirect('/login')->with(['toast' => $toastData]);
    }

    protected function sendMaximumActiveSessionResponse()
    {
        $toastData = [
            'title' => trans('update.login_failed'),
            'msg' => trans('update.device_limit_reached_please_try_again'),
            'status' => 'error'
        ];

        return redirect('/login')->with(['login_failed_active_session' => $toastData]);
    }
    public function afterLogged(Request $request, $verify = false, $redirectTo = null)
    {
        $user = auth()->user();

        // Check for banned status
        if ($user->ban) {
            $time = time();
            $endBan = $user->ban_end_at;
            if (!empty($endBan) && $endBan > $time) {
                $this->guard()->logout();
                $request->session()->flush();
                $request->session()->regenerate();

                return $this->sendBanResponse($request, $user);
            } elseif (!empty($endBan) && $endBan < $time) {
                // Unban the user
                $user->update([
                    'ban' => false,
                    'ban_start_at' => null,
                    'ban_end_at' => null,
                ]);
            }
        }

        // Handle verification status
        if ($user->status != User::$active && !$verify) {
            $this->guard()->logout();
            $request->session()->flush();
            $request->session()->regenerate();

            $verificationController = new VerificationController();
            $checkConfirmed = $verificationController->checkConfirmed($user, $this->username(), $request->get('username'));

            if ($checkConfirmed['status'] == 'send') {
                return redirect('/verification');
            } elseif ($checkConfirmed['status'] == 'verified') {
                $user->update(['status' => User::$active]);
            }
        } elseif ($verify) {
            session()->forget('verificationId');
            $user->update(['status' => User::$active]);

            $registerReward = RewardAccounting::calculateScore(Reward::REGISTER);
            RewardAccounting::makeRewardAccounting($user->id, $registerReward, Reward::REGISTER, $user->id, true);
        }

        // If the user's status is not active, log them out
        if ($user->status != User::$active) {
            $this->guard()->logout();
            $request->session()->flush();
            $request->session()->regenerate();

            return $this->sendNotActiveResponse($user);
        }

        // Check for login device limit
        $checkLoginDeviceLimit = $this->checkLoginDeviceLimit($user);
        if ($checkLoginDeviceLimit != "ok") {
            $this->guard()->logout();
            $request->session()->flush();
            $request->session()->regenerate();

            return $this->sendMaximumActiveSessionResponse();
        }

        // Update logged count and store login history
        $user->update(['logged_count' => (int)$user->logged_count + 1]);

        $cartManagerController = new CartManagerController();
        $cartManagerController->storeCookieCartsToDB();

        $userLoginHistoryMixin = new UserLoginHistoryMixin();
        $userLoginHistoryMixin->storeUserLoginHistory($user);

           // Redirect based on the 'redirectTo' URL or user role
    $redirectTo = session()->pull('intended_url', '/'); // Use session-stored intended URL if available


        // Redirect based on the 'redirectTo' URL or user role
        if ($redirectTo) {
            return redirect($redirectTo); // Redirect to the URL passed as 'redirect_to'
        }

        // Default redirection for admin or regular user
        if ($user->isAdmin()) {
            return redirect(getAdminPanelUrl());
        } else {
            return redirect('/panel');
        }
    }


    private function checkLoginDeviceLimit($user)
    {
        $securitySettings = getGeneralSecuritySettings();

        if (!empty($securitySettings) and !empty($securitySettings['login_device_limit'])) {
            $limitCount = !empty($securitySettings['number_of_allowed_devices']) ? $securitySettings['number_of_allowed_devices'] : 1;

            $count = $user->logged_count;

            if ($count >= $limitCount) {
                return "no";
            }
        }

        return 'ok';
    }

    protected function validator(array $data)
    {
        $registerMethod = getGeneralSettings('register_method') ?? 'mobile';

        if (!empty($data['mobile']) and !empty($data['country_code'])) {
            $data['mobile'] = ltrim($data['country_code'], '+') . ltrim($data['mobile'], '0');
        }

        $rules = [
            'country_code' => ($registerMethod == 'mobile') ? 'required' : 'nullable',
            'mobile' => (($registerMethod == 'mobile') ? 'required' : 'nullable') . '|numeric|unique:users',
            'email' => (($registerMethod == 'email') ? 'required' : 'nullable') . '|email|max:255|unique:users',
            'term' => 'required',
            'full_name' => 'required|string|min:3',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|same:password',
            'referral_code' => 'nullable|exists:affiliates_codes,code'
        ];

        if (!empty(getGeneralSecuritySettings('captcha_for_register'))) {
            $rules['captcha'] = 'required|captcha';
        }

        return Validator::make($data, $rules, [], [
            'mobile' => trans('auth.mobile'),
            'email' => trans('auth.email'),
            'term' => trans('update.terms'),
            'full_name' => trans('auth.full_name'),
            'password' => trans('auth.password'),
            'password_confirmation' => trans('auth.password_repeat'),
            'referral_code' => trans('financial.referral_code'),
        ]);
    }
}