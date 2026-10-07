<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        // Como a entrada é só nome + código, limitamos as tentativas seguidas.
        // O limite por aparelho é folgado porque vários professores podem
        // usar a mesma internet da escola ao mesmo tempo.
        RateLimiter::for('acesso', fn (Request $request) => [
            Limit::perMinute(10)->by('nome:'.$request->ip().'|'.Str::lower((string) $request->input('nome'))),
            Limit::perMinute(60)->by('ip:'.$request->ip()),
        ]);

        // Editar a pauta e trocar os códigos: só contas da coordenação.
        Gate::define('coordenar', fn (User $usuario) => $usuario->ehCoordenador());
    }
}
