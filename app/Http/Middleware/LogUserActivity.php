<?php namespace gesecole\Http\Middleware;

use Closure;
use gesecole\ActivityLog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class LogUserActivity {
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if(Auth::check() && $this->ensureActivityLogSchema() && $this->shouldLog($request)) {
            ActivityLog::create(array(
                'user_id' => Auth::user()->id,
                'user_name' => Auth::user()->fullname ? Auth::user()->fullname : Auth::user()->name,
                'method' => $request->method(),
                'route_name' => $this->routeName($request),
                'uri' => $request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->header('User-Agent'), 0, 255),
                'payload' => json_encode($this->cleanPayload($request)),
                'status_code' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null,
                'created_at' => date('Y-m-d H:i:s'),
            ));
        }

        return $response;
    }

    private function shouldLog($request)
    {
        if(in_array($request->method(), array('POST', 'PUT', 'PATCH', 'DELETE'))) {
            return true;
        }

        $signature = strtolower($this->routeName($request).' '.$request->path());
        $keywords = array(
            'export',
            'pdf',
            'excel',
            'download',
            'print',
            'imprimer',
            'generate',
            'generer',
            'certificat',
            'attestation',
            'releve',
            'releves',
            'pv',
            'historique'
        );

        foreach($keywords as $keyword) {
            if(strpos($signature, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    private function routeName($request)
    {
        $route = $request->route();
        if(!$route) {
            return $request->method().' '.$request->path();
        }

        if(method_exists($route, 'getName') && $route->getName()) {
            return $route->getName();
        }

        if(method_exists($route, 'getActionName') && $route->getActionName()) {
            return $route->getActionName();
        }

        if(method_exists($route, 'uri')) {
            return $request->method().' '.$route->uri();
        }

        return $request->method().' '.$request->path();
    }

    private function cleanPayload($request)
    {
        $excluded = array('_token', '_method', 'password', 'password_confirmation', 'current_password');
        $payload = $request->except($excluded);
        $clean = array();

        foreach($payload as $key => $value) {
            if(is_array($value)) {
                $clean[$key] = array_slice($value, 0, 50, true);
            }
            elseif(is_object($value)) {
                $clean[$key] = '[objet]';
            }
            else {
                $clean[$key] = is_string($value) ? substr($value, 0, 255) : $value;
            }
        }

        return $clean;
    }

    private function ensureActivityLogSchema()
    {
        if(Schema::hasTable('activity_logs')) {
            return true;
        }

        Schema::create('activity_logs', function(Blueprint $table) {
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

        return Schema::hasTable('activity_logs');
    }
}
