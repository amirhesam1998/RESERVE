<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Policies\UserPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {


        /*         Gate::policy(User::class, UserPolicy::class);

        Gate::before(function (User $user, string $ability) {
            if ($user->hasPermission($ability)) {
                return true;
            }
        }); */


        // ---------- USERS GATES --------------------
        Gate::define('users-onlyPermission', function (User $currentUser, string $ability) {

            if ($currentUser->hasPermission($ability)) {
                return true;
            }

            return false;
        });

        Gate::define('showone_user', function (User $currentUser, User $targetUser, string $ability) {
            if ($currentUser->level === 'creator') {
                return true;
            }

            if ($targetUser->level === 'creator') {
                return false;
            }

            if ($currentUser->id === $targetUser->id) {
                return true;
            }

            if ($currentUser->hasPermission($ability)) {
                return true;
            };

            return false;
        });

        Gate::define('users', function (User $currentUser, User $targetUser, string $ability) {
            if ($targetUser->level === 'creator') {
                return false;
            }

            if ($currentUser->id === $targetUser->id) {
                return true;
            }

            if ($currentUser->hasPermission($ability)) {
                return true;
            }

            return false;
        });

        Gate::define('delete_user', function (User $currentUser, User $targetUser, string $ability) {
            if ($targetUser->level === 'creator') {
                return false;
            }

            if ($currentUser->id === $targetUser->id) {
                return true;
            }

            if ($currentUser->hasPermission($ability)) {
                return true;
            }

            return false;
        });


        Gate::define('user-editPass', function (User $currentUser, User $targetUser, string $ability) {
            if ($targetUser->level === 'creator') {
                return false;
            }

            if ($currentUser->id === $targetUser->id) {
                return true;
            }

            if ($currentUser->hasPermission($ability)) {
                return true;
            }

            return false;
        });

        // ----------------------- ROLES GATES -----------------------------
        Gate::define('roles', function (User $user, string $ability) {
            if ($user->hasPermission($ability)) {
                return true;
            }

            return false;
        });

        /*         Gate::define('roles-target', function (User $currentUser, Role $targetRole, string $ability) {
            if ($currentUser->hasPermission($ability)) {
                return true;
            }

            return false;
        }); */

        // ------------------------ CATEGORY GATES --------------------------
        Gate::define('categories', function (User $currentUser, string $abilitty) {
            if ($currentUser->hasPermission($abilitty)) {
                return true;
            }

            return false;
        });

        Gate::define('categories-create', function (User $currentUser, string $ability) {
            if ($currentUser->hasPermission($ability)) {
                return true;
            }

            return false;
        });

        Gate::define('categories-show', function (User $currentUser, string $ability) {
            if ($currentUser->hasPermission($ability)) {
                return true;
            }

            return false;
        });

        //---------------------------------- SALON GATES ----------------------------
        Gate::define('salons', function (User $currentUser, string $ability) {
            if ($currentUser->hasPermission($ability)) {
                return true;
            }

            return false;
        });
    }
}
