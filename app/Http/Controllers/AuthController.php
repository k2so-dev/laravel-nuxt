<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register new user
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
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
     * Generate sanctum token on successful login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        $request->authenticate($user);

        return response()->json([
            'ok' => true,
            'user' => $user,
            'token' => $user->createToken(
                $request->userAgent(),
                ['*'],
                $request->remember ?
                    now()->addMonth() :
                    now()->addDay()
            )->plainTextToken,
        ]);
    }

    /**
     * Revoke token; only remove token that is used to perform logout (i.e. will not revoke all tokens)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

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
                'must_verify_email' => $user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail(),
                'roles' => $user->roles()->select('name')->pluck('name'),
            ],
        ]);
    }

    /**
     * Handle an incoming password reset link request.
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

        if ($status != Password::RESET_LINK_SENT) {
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
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
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
    public function verifyEmail(Request $request, $ulid, $hash): JsonResponse
    {
        $user = User::whereUlid($ulid)->first();

        abort_unless($user, 404);
        abort_unless(hash_equals(sha1($user->getEmailForVerification()), $hash), 403, __('Invalid verification link'));

        if (! $user->hasVerifiedEmail()) {
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

        $user = User::where('email', $request->email)->whereNull('email_verified_at')->first();
        abort_unless($user, 400);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'ok' => true,
            'message' => __('Verification link sent!'),
        ]);
    }
}
