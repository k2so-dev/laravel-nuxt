<?php

namespace App\Services\Auth;

use App\Contracts\AuthServiceContract;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiAuthService implements AuthServiceContract
{
    public function login(Request $request, User $user): array
    {
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $token = $user->createDeviceToken(
            device: $request->deviceName(),
            ip: $request->ip(),
            remember: $request->input('remember', false)
        );

        return [
            'ok' => true,
            'token' => $token,
        ];
    }

    public function logout(Request $request): void
    {
        $request->user()->currentAccessToken()->delete();
    }

    public function handleCallback(Request $request, User $user): array
    {
        $token = $user->createDeviceToken(
            device: $request->deviceName(),
            ip: $request->ip(),
            remember: $request->input('remember', false)
        );

        return [
            'ok' => true,
            'provider' => $request->route('provider'),
            'token' => $token,
        ];
    }

    public function getDevices(Request $request): array
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        $devices = $user->tokens()
            ->select('id', 'name', 'ip', 'last_used_at')
            ->orderBy('last_used_at', 'DESC')
            ->get()
            ->map(function ($device) use ($currentToken) {
                $device->key = Crypt::encryptString($device->id);
                $device->is_current = $currentToken->id === $device->id;
                unset($device->id);

                return $device;
            });

        return $devices->toArray();
    }

    public function disconnectDevice(Request $request): void
    {
        $id = (int) Crypt::decryptString($request->key);

        if (!empty($id)) {
            $request->user()->tokens()->where('id', $id)->delete();
        }
    }
}
