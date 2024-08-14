<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapApiAdminRoutes();

        $this->mapApiFarmRoutes();

        $this->mapApiEmpresaRoutes();

        $this->mapApiMobileRoutes();

        $this->mapApiUnidadeSanitariaRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api/guest')
             ->middleware('api', 'cors')
             ->namespace($this->namespace."\\API")
             ->group(base_path('routes/api.php'));
    }

    /**
     * Define the "api" routes for the admin application section.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiAdminRoutes()
    {
        Route::prefix('api/admin')
             ->middleware('api', 'cors', 'auth.jwt')
             ->namespace($this->namespace."\\API")
             ->group(base_path('routes/api_admin.php'));
    }

    protected function mapApiFarmRoutes()
    {
        Route::prefix('api/farm')
             ->middleware('api', 'cors', 'auth.jwt', 'farmacia')
             ->namespace($this->namespace."\\API\\Farmacia")
             ->group(base_path('routes/api_farm.php'));
    }

    protected function mapApiUnidadeSanitariaRoutes()
    {
        Route::prefix('api/uni_sanit')
             ->middleware('api', 'cors', 'auth.jwt', 'unidade_sanitaria')
             ->namespace($this->namespace."\\API\\UnidadeSanitaria")
             ->group(base_path('routes/api_unidade_sanitaria.php'));
    }
    
    protected function mapApiEmpresaRoutes()
    {
        Route::prefix('api/empresa')
             ->middleware('api', 'cors', 'auth.jwt', 'empresa')
             ->namespace($this->namespace."\\API\\Empresa")
             ->group(base_path('routes/api_empresa.php'));
    }

    protected function mapApiMobileRoutes()
    {
        Route::prefix('api/mobile')
             ->middleware('api', 'cors', 'assign.guard:cliente')
             ->namespace($this->namespace."\\API\\Mobile")
             ->group(base_path('routes/api_mobile.php'));
    }
}
