<?php

namespace App\Http\Controllers;

use App\Models\TemporaryUpload;
use App\Rules\TemporaryFileExists;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    /**
     * Update the user's profile information.
     */
    public function update(Request $request): JsonResponse
    {
        $request->merge([
            // Remove extra spaces and non-word characters
            'name' => $request->name ? Str::squish(Str::onlyWords($request->name)) : '',
        ]);

        $user = $request->user();

        $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
            'avatar' => ['nullable', 'string', Rule::excludeIf($request->avatar === $user->avatar), 'regex:/^avatars\/[a-z0-9]{26}\.([a-z]++)$/i', new TemporaryFileExists],
        ]);

        if ($user->avatar && Str::startsWith($user->avatar, 'avatars/') && $user->avatar !== $request->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $email = $user->email;

        $user->update($request->only(['name', 'email', 'avatar']));

        if ($email !== $request->email) {
            $user->email_verified_at = null;
            $user->save(['timestamps' => false]);
            $user->sendEmailVerificationNotification();
        }

        // Delete temporary upload record
        TemporaryUpload::where('path', $request->avatar)->delete();

        return response()->json([
            'ok' => true,
        ]);
    }

    /**
     * Update the user's password.
     * @throws ValidationException
     */
    public function password(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string', 'min:8', 'max:100'],
            'password' => ['required', 'string', 'min:8', 'max:100', 'confirmed'],
        ]);

        $user = $request->user();
        abort_unless($user->has_password, 403, __('Access denied.'));

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('auth.password'),
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'ok' => true,
        ]);
    }
}
