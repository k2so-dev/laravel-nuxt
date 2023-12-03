<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create roles and assign existing permissions
        $role1 = Role::create(['name' => 'admin']);
        $role2 = Role::create(['name' => 'user']);

        // create admin user
        $user = \App\Models\User::factory()->create([
            'name' => 'Super',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);

        $user->ulid = Str::ulid()->toBase32();
        $user->email_verified_at = now();
        $user->save(['timestamps' => false]);

        $user->assignRole($role1);
    }
}
