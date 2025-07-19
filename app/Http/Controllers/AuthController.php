<?php

namespace App\Http\Controllers;

use App\Helpers\Utils;
use App\Models\User;
use App\Models\UserProvider;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Register new user
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->ulid = Str::ulid()->toBase32();
        $user->save();

        $user->assignRole('user');

        event(new Registered($user));

        return response()->json([
            'ok' => true,
        ], 201);
    }

    /**
     * Redirect to provider for authentication
     */
    public function redirect(Request $request, string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Handle callback from provider
     * @throws \Exception
     */
    public function callback(Request $request, string $provider): View
    {
        $oAuthUser = Socialite::driver($provider)->stateless()->user();

        if (!$oAuthUser?->token) {
            return view('oauth', [
                'message' => [
                    'ok' => false,
                    'message' => __('Unable to authenticate with :provider', ['provider' => $provider]),
                ],
            ]);
        }

        $userProvider = UserProvider::select('id', 'user_id')
            ->where('name', $provider)
            ->where('provider_id', $oAuthUser->id)
            ->first();

        if (!$userProvider) {
            if (User::where('email', $oAuthUser->email)->exists()) {
                return view('oauth', [
                    'message' => [
                        'ok' => false,
                        'message' => __('Unable to authenticate with :provider. User with email :email already exists. To connect a new service to your account, you can go to your account settings and go through the process of linking your account.', [
                            'provider' => $provider,
                            'email' => $oAuthUser->email,
                        ]),
                    ],
                ]);
            }

            $user = new User();
            $user->ulid = Str::ulid()->toBase32();
            $user->avatar = $oAuthUser->picture ?? $oAuthUser->avatar_original ?? $oAuthUser->avatar;
            $user->name = $oAuthUser->name;
            $user->email = $oAuthUser->email;
            $user->password = null;
            $user->email_verified_at = now();
            $user->save();

            $user->assignRole('user');

            $user->userProviders()->create([
                'provider_id' => $oAuthUser->id,
                'name' => $provider,
            ]);
        } else {
            $user = $userProvider->user;
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return view('oauth', [
            'message' => [
                'ok' => true,
                'provider' => $provider,
            ],
        ]);
    }

    /**
     * Login user
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'ok' => true,
        ]);
    }

    /**
     * Revoke token; only remove token that is used to perform logout (i.e. will not revoke all tokens)
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::logout();

        return response()->json([
            'ok' => true,
        ]);
    }

    /**
     * Get authenticated user details
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'ok' => true,
            'user' => [
                ...$user->toArray(),
                'must_verify_email' => $user->mustVerifyEmail(),
                'has_password' => (bool) $user->password,
                'roles' => $user->roles()->select('name')->pluck('name'),
                'providers' => $user->userProviders()->select('name')->pluck('name'),
            ],
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     * @throws ValidationException
     */
    public function sendResetPasswordLink(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'ok' => true,
            'message' => __($status),
        ]);
    }

    /**
     * Handle an incoming new password request.
     * @throws ValidationException
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email', 'exists:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            static function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'ok' => true,
            'message' => __($status),
        ]);
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verifyEmail(Request $request, string $ulid, string $hash): JsonResponse
    {
        $user = User::where('ulid', $ulid)->first();

        abort_if(!$user, 404);
        abort_if(!hash_equals(sha1($user->getEmailForVerification()), $hash), 403, __('Invalid verification link'));

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();

            event(new Verified($user));
        }

        return response()->json([
            'ok' => true,
        ]);
    }

    /**
     * Send a new email verification notification.
     */
    public function verificationNotification(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = $request->user() ?: User::where('email', $request->email)->whereNull('email_verified_at')->first();

        abort_if(!$user, 400);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'ok' => true,
            'message' => __('Verification link sent!'),
        ]);
    }

    /**
     * Get authenticated user devices
     */
    public function devices(Request $request): JsonResponse
    {
        $user = $request->user();

        $currentSessionId = $request->session()->getId();

        $devices = $user->sessions()
            ->select(['id as key', 'ip_address as ip', 'user_agent as name', 'last_activity'])
            ->orderBy('last_activity', 'DESC')
            ->get()
            ->map(function ($device) use ($currentSessionId) {
                $device->is_current = $currentSessionId === $device->key;
                $device->name = Utils::getDeviceNameFromDetector(Utils::getDeviceDetectorByUserAgent($device->name));
                $device->last_used_at = now()->parse($device->last_activity);

                return $device;
            });

        return response()->json([
            'ok' => true,
            'devices' => $devices,
        ]);
    }

    /**
     * Disconnect device by id
     */
    public function deviceDisconnect(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required|size:40',
        ]);

        $user = $request->user();
        $user->sessions()->where('id', $request->key)->delete();

        return response()->json([
            'ok' => true,
        ]);
    }
}
