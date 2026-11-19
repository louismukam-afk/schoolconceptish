<?php

use gesecole\Action;
use gesecole\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use gesecole\Http\Controllers\eleves\disciplineControlleretd;

class DisciplineEtdRoutesTest extends TestCase
{
    public function createApplication()
    {
        global $crypt;
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        return $app;
    }

    private function routeAction($method, $path)
    {
        $route = $this->app['router']->getRoutes()->match(Request::create($path, $method));
        return $route->getAction()['action'];
    }

    public function testDisciplineEntryAndCallDoNotRequireNotePermissions()
    {
        $call = array(Action::ACTION_STUDENT_DISCIPLINE_ETD_CREATE, Action::ACTION_STUDENT_DISCIPLINE_ETD_UPDATE);
        $this->assertEquals(Action::ACTION_STUDENT, $this->routeAction('GET', '/eleves'));
        $this->assertEquals(array_merge($call, array(Action::ACTION_STUDENT_DISCIPLINE_ETD_REPORT)),
            $this->routeAction('GET', '/eleves/disciplineetd'));
        $this->assertEquals($call, $this->routeAction('GET', '/eleves/disciplineetd1'));
        $this->assertEquals($call, $this->routeAction('POST', '/eleves/appel/store'));
    }

    public function testReportsUseTheirOwnDisciplinePermission()
    {
        $this->assertEquals(Action::ACTION_STUDENT_DISCIPLINE_ETD_REPORT,
            $this->routeAction('GET', '/eleves/discipline_etat'));
        $this->assertEquals(Action::ACTION_STUDENT_DISCIPLINE_ETD_REPORT,
            $this->routeAction('POST', '/eleves/discipline_etat/generer'));
    }

    public function testDisciplinePermissionsAreIndependentFromNotesAndWrites()
    {
        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        Schema::create('actions', function ($table) {
            $table->increments('id');
            $table->integer('id_user');
            $table->string('action_name');
        });
        Schema::create('discipline__new__etds', function ($table) {
            $table->increments('id');
            $table->integer('id_eleve');
            $table->integer('id_horaire');
            $table->date('date_discipline');
        });

        $user = new User;
        $user->id = 910001;
        Auth::setUser($user);
        DB::table('actions')->insert(array('id_user' => $user->id,
            'action_name' => Action::ACTION_STUDENT_DISCIPLINE_ETD_CREATE));

        $this->assertTrue($user->canDoAction(Action::ACTION_STUDENT_DISCIPLINE_ETD_CREATE));
        $this->assertFalse($user->canDoAction(Action::ACTION_STUDENT_DISCIPLINE_ETD_UPDATE));
        $this->assertFalse($user->canDoAction(Action::ACTION_STUDENT_DISCIPLINE_ETD_REPORT));
        $this->assertFalse($user->canDoAction(Action::ACTION_STUDENT_NOTE));

        DB::table('discipline__new__etds')->insert(array('id_eleve' => 1,
            'id_horaire' => 1, 'date_discipline' => '2026-11-19'));
        session(array('year' => '2026-2027'));
        $request = Request::create('/eleves/appel/store', 'POST', array(
            'date_discipline' => '2026-11-19',
            'presence' => array(1 => array(1 => 1, 2 => 1))));
        try {
            (new disciplineControlleretd)->storePresence($request);
            $this->fail('Updating an existing call must require the update permission.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertEquals(403, $exception->getStatusCode());
        }
        $this->assertEquals(1, DB::table('discipline__new__etds')->count());

        DB::table('actions')->where('id_user', $user->id)->update(array(
            'action_name' => Action::ACTION_STUDENT_DISCIPLINE_ETD_UPDATE));
        $this->assertFalse($user->canDoAction(Action::ACTION_STUDENT_DISCIPLINE_ETD_CREATE));
        $this->assertTrue($user->canDoAction(Action::ACTION_STUDENT_DISCIPLINE_ETD_UPDATE));
        $request = Request::create('/eleves/appel/store', 'POST', array(
            'date_discipline' => '2026-11-19',
            'presence' => array(2 => array(1 => 1))));
        try {
            (new disciplineControlleretd)->storePresence($request);
            $this->fail('A new call must require the create permission.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertEquals(403, $exception->getStatusCode());
        }
        $this->assertEquals(1, DB::table('discipline__new__etds')->count());

        DB::table('actions')->where('id_user', $user->id)->update(array(
            'action_name' => Action::ACTION_STUDENT_DISCIPLINE_ETD_REPORT));
        $this->assertTrue($user->canDoAction(Action::ACTION_STUDENT_DISCIPLINE_ETD_REPORT));
        $this->assertFalse($user->canDoAction(Action::ACTION_STUDENT_NOTE));
    }
}
