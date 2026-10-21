<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model {
    protected $table = 'activity_logs';
    public $timestamps = false;

    protected $fillable = array(
        'user_id',
        'user_name',
        'method',
        'route_name',
        'uri',
        'ip_address',
        'user_agent',
        'payload',
        'status_code',
        'created_at'
    );

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getPayloadArrayAttribute()
    {
        $payload = json_decode($this->payload, true);
        return is_array($payload) ? $payload : array();
    }
}
