<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

class AccessRole extends Model {
    protected $table = 'access_roles';

    protected $fillable = ['name', 'description'];

    public function actions()
    {
        return $this->hasMany(AccessRoleAction::class, 'id_access_role');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'access_role_user', 'id_access_role', 'id_user')->withTimestamps();
    }
}
