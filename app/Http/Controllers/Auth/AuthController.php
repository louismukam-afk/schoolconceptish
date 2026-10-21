<?php namespace gesecole\Http\Controllers\Auth;

use gesecole\Http\Controllers\Controller;
use gesecole\Campus;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;

class AuthController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Registration & Login Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles the registration of new users, as well as the
	| authentication of existing users. By default, this controller uses
	| a simple trait to add these behaviors. Why don't you explore it?
	|
	*/

    protected $username = 'name';


    use AuthenticatesAndRegistersUsers;

	/**
	 * Create a new authentication controller instance.
	 *
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
	 * @return void
	 */
	public function __construct(Guard $auth, Registrar $registrar)
	{
		$this->auth = $auth;
		$this->registrar = $registrar;

		$this->middleware('guest', ['except' => 'getLogout']);
	}

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'name' => 'required', 'password' => 'required',
        ]);

        $credentials = $request->only('name', 'password');

        if ($this->auth->attempt($credentials, $request->has('remember')))
        {
            $user = $this->auth->user();
            $campusCount = $user->isAdmin() ? Campus::count() : $user->campus()->count();
            if ($campusCount > 1) {
                return redirect()->to(url('/select-campus'));
            }
            if ($campusCount == 1) {
                $campus = $user->isAdmin() ? Campus::first() : $user->campus()->first();
                if ($campus) {
                    session(array('campus_id' => $campus->id, 'campus_nom' => $campus->nom));
                }
            }
            return redirect()->intended($this->redirectPath());
        }

        return redirect($this->loginPath())
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => $this->getFailedLoginMessage(),
            ]);
    }

}
