<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Http\Request;

interface AuthServiceContract
{
    public function login(Request $request, User $user): array;
    public function logout(Request $request): void;
    public function handleCallback(Request $request, User $user): array;
    public function getDevices(Request $request): array;
    public function disconnectDevice(Request $request): void;
}
