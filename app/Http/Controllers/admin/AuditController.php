<?php namespace gesecole\Http\Controllers\admin;

use gesecole\ActivityLog;
use gesecole\Http\Controllers\Controller;
use gesecole\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AuditController extends Controller {
    public function __construct()
    {
        session(array('big_title' => trans('admin.dashboard')));
        $this->renewSchoolYear();
    }

    public function index(Request $request)
    {
        $this->ensureActivityLogSchema();

        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        $idUser = $request->input('id_user');
        $method = $request->input('method');
        $routeName = $request->input('route_name');

        $query = ActivityLog::with('user');

        if($dateDebut) {
            $query->whereRaw('DATE(created_at) >= ?', array($dateDebut));
        }

        if($dateFin) {
            $query->whereRaw('DATE(created_at) <= ?', array($dateFin));
        }

        if($idUser) {
            $query->where('user_id', '=', $idUser);
        }

        if($method) {
            $query->where('method', '=', $method);
        }

        if($routeName) {
            $query->where('route_name', 'like', '%'.$routeName.'%');
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);
        $logs->appends($request->except('page'));

        $values = array(
            'titre' => 'Journal des operations',
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'id_user' => $idUser,
            'method' => $method,
            'route_name' => $routeName,
            'users' => User::orderBy('fullname')->orderBy('name')->get(),
            'methods' => array('GET', 'POST', 'PUT', 'PATCH', 'DELETE'),
            'logs' => $logs,
        );

        return view('admin.audit.index', $values);
    }

    private function ensureActivityLogSchema()
    {
        if(Schema::hasTable('activity_logs')) {
            return;
        }

        Schema::create('activity_logs', function($table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('method', 20);
            $table->string('route_name')->nullable();
            $table->string('uri');
            $table->string('ip_address', 60)->nullable();
            $table->string('user_agent')->nullable();
            $table->text('payload')->nullable();
            $table->integer('status_code')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }
}
