<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // List of global middleware 
        // Add other middleware as needed
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            // Add other web-specific middleware here
        ],
        // 'api' => [
        //     // API middleware can be defined here
        //     'throttle:api',
        // ],

        'api' => [
            //\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            //'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

        ],
      

    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'permission' => \App\Http\Middleware\PermissionMiddleware::class,
        'jwt.auth' => \App\Http\Middleware\CheckJWTToken::class,
        'custom.token' => \App\Http\Middleware\ValidateCustomToken::class,
    ];
}
