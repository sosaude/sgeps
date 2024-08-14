<?php

namespace App\Providers;
use Gate;
use App\Models\Permissao;
use Illuminate\Support\ServiceProvider;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if(app()->runningInConsole()) {
            return;
        }
        Permissao::get()->map(function ($permissao) {
            Gate::define($permissao->nome, function ($user) use ($permissao) {
                return $user->hasPermissionTo($permissao);
            });
        });
    }
}
