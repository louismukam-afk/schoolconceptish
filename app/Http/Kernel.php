<?php namespace gesecole\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {

	/**
	 * The application's global HTTP middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = [
        'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
		'Illuminate\Cookie\Middleware\EncryptCookies',
		'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
		'Illuminate\Session\Middleware\StartSession',
       'gesecole\Http\Middleware\authMiddleware',
        'Illuminate\View\Middleware\ShareErrorsFromSession',
        'gesecole\Http\Middleware\VerifyCsrfToken',
        'gesecole\Http\Middleware\locale',
        'gesecole\Http\Middleware\LogUserActivity',


    ];

	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [
        'start' => 'gesecole\Http\Middleware\startMiddleware',
        'dc'=>'gesecole\Http\Middleware\dcMiddleware',
        'lvl0' => 'gesecole\Http\Middleware\privileges\g_king',
        'lvl1' => 'gesecole\Http\Middleware\privileges\g_admin',
        'lvl2' => 'gesecole\Http\Middleware\privileges\g_editeur',
        'lvl16' => 'gesecole\Http\Middleware\privileges\g_comptabilite',
        'lvl17' => 'gesecole\Http\Middleware\privileges\g_personnel',
        'lvl18' => 'gesecole\Http\Middleware\privileges\g_notes',
        'lvl18.5' => 'gesecole\Http\Middleware\privileges\g_enseignants',
        'lvl19' => 'gesecole\Http\Middleware\privileges\g_inscriptions',
        'action' => 'gesecole\Http\Middleware\ActionMiddleware',
        'auth' => 'gesecole\Http\Middleware\Authenticate',
		'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
		'guest' => 'gesecole\Http\Middleware\RedirectIfAuthenticated',
	];

}
