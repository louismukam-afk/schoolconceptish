<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

class AccessRoleAction extends Model {
    protected $table = 'access_role_actions';

    protected $fillable = ['id_access_role', 'action_name'];

    public function role()
    {
        return $this->belongsTo(AccessRole::class, 'id_access_role');
    }
}
