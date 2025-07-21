<?php

namespace App\Services\Auth;

use App\Contracts\AuthServiceContract;
use App\Helpers\Utils;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class WebAuthService implements AuthServiceContract
{
    public function login(Request $request, User $user): array
    {
        if (!Auth::attempt($request->only('email', 'password'), $request->remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return ['ok' => true];
    }

    public function logout(Request $request): void
    {
        Auth::logout();
    }

    public function handleCallback(Request $request, User $user): array
    {
        Auth::login($user, true);
        $request->session()->regenerate();

        return [
            'ok' => true,
            'provider' => $request->route('provider'),
        ];
    }

    public function getDevices(Request $request): array
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

        return $devices->toArray();
    }

    public function disconnectDevice(Request $request): void
    {
        $request->user()->sessions()->where('id', $request->key)->delete();
    }
}
