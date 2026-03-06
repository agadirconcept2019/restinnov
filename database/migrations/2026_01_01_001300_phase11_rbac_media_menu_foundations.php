<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'user_id']);
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::table('media', function (Blueprint $table) {
            $table->softDeletes();
        });

        $roles = config('permissions.roles', []);
        $permissions = config('permissions.permissions', []);
        $rolePermissions = config('permissions.role_permissions', []);

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'slug' => $role,
                'name' => str($role)->replace('_', ' ')->title()->toString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert([
                'slug' => $permission,
                'name' => str($permission)->replace('.', ' ')->title()->toString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $roleMap = DB::table('roles')->pluck('id', 'slug');
        $permissionMap = DB::table('permissions')->pluck('id', 'slug');

        foreach ($rolePermissions as $role => $grants) {
            if (! isset($roleMap[$role])) {
                continue;
            }

            $assign = $grants === ['*'] ? array_keys($permissionMap->all()) : $grants;
            foreach ($assign as $permission) {
                $permissionId = $permissionMap[$permission] ?? null;
                if (! $permissionId) {
                    continue;
                }

                DB::table('permission_role')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleMap[$role],
                ]);
            }
        }

        $ownerRoleId = $roleMap['owner'] ?? null;
        if ($ownerRoleId) {
            $ownerIds = DB::table('users')->where('role', 'owner')->pluck('id');
            foreach ($ownerIds as $ownerId) {
                DB::table('role_user')->insertOrIgnore(['role_id' => $ownerRoleId, 'user_id' => $ownerId]);
            }
        }

        $adminRoleId = $roleMap['admin'] ?? null;
        if ($adminRoleId) {
            $adminIds = DB::table('users')->where('role', '!=', 'owner')->pluck('id');
            foreach ($adminIds as $adminId) {
                DB::table('role_user')->insertOrIgnore(['role_id' => $adminRoleId, 'user_id' => $adminId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
