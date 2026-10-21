<?php namespace gesecole;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * gesecole\User
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $remember_token
 * @property string $role
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\User whereRole($value)
 * @property string $fullname
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\User whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\User whereUpdatedAt($value)
 * @property int $active
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\User whereActive($value)
 * @property int|null $id_personnel
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\User whereIdPersonnel($value)
 */
class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'fullname', 'password','role','active','id_personnel'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

	public function canDoAction($actionAgainst) {

        if(is_array($actionAgainst)) {
            foreach($actionAgainst as $action) {
                if($this->canDoAction($action)) {
                    return true;
                }
            }
            return false;
        }

        if($this->isAdmin()) {
            return true;
        }
        $a = Action::where('action_name', 'like', $actionAgainst.'%')
            ->where('id_user', '=', $this->id)->first();
        if($a != null) {
            return true;
        }
        foreach($this->legacyActionParents($actionAgainst) as $parentAction) {
            $parent = Action::where('action_name', '=', $parentAction)
                ->where('id_user', '=', $this->id)->first();
            if($parent != null) {
                return true;
            }
        }
        if($this->hasAccessRoleAction($actionAgainst)) {
            return true;
        }
        foreach($this->legacyActionParents($actionAgainst) as $parentAction) {
            if($this->hasAccessRoleAction($parentAction, true)) {
                return true;
            }
        }
	    return false;
    }

    public function isAdmin() {
        if(isset($this->is_super_admin) && $this->is_super_admin) {
            return true;
        }

        $a = Action::whereRaw('(action_name = ? OR action_name = ?) AND id_user = ?',[Action::ACTION_ALL, Action::ACTION_ALMOST_ALL, $this->id])->first();
        if($a != null) {
            return true;
        }

        return false;
    }

    public function isCreator() {
	    return $this->hasSpecificAction(Action::ACTION_ALL);
    }
    public function isTeacher() {
	    return $this->hasSpecificAction(Action::ACTION_TEACHER);
    }



    public function hasSpecificAction($actionAgainst) {
	    $a = Action::where('action_name', '=', $actionAgainst)
            ->where('id_user', '=', $this->id)->first();
        if($a != null) {
            return true;
        }
        if($this->hasAccessRoleAction($actionAgainst, true)) {
            return true;
        }
        return false;
    }

    public function actions() {
        return  $this->hasMany(Action::class,'id_user');

    }
    public function roles()
    {
        return  $this->hasMany(Role::class,'id_user');
    }

    public function accessRoles()
    {
        return $this->belongsToMany(AccessRole::class, 'access_role_user', 'id_user', 'id_access_role')->withTimestamps();
    }

    private function hasAccessRoleAction($actionAgainst, $exact = false)
    {
        if(!Schema::hasTable('access_role_user') || !Schema::hasTable('access_role_actions')) {
            return false;
        }

        $query = DB::table('access_role_user')
            ->join('access_role_actions', 'access_role_user.id_access_role', '=', 'access_role_actions.id_access_role')
            ->where('access_role_user.id_user', '=', $this->id);

        if($exact) {
            $query->where('access_role_actions.action_name', '=', $actionAgainst);
        }
        else {
            $query->where('access_role_actions.action_name', 'like', $actionAgainst.'%');
        }

        return $query->first() != null;
    }

    private function legacyActionParents($actionAgainst)
    {
        $classSettings = [
            'ACTION_ADMIN_CYCLE',
            'ACTION_ADMIN_NNIVEAU',
            'ACTION_ADMIN_FILIERE',
            'ACTION_ADMIN_SPECIALITE',
            'ACTION_ADMIN_TUTELLE',
            'ACTION_ADMIN_MATIERE',
            'ACTION_ADMIN_PROGRAMME',
            'ACTION_ADMIN_SESSION_EXAM',
            'ACTION_ADMIN_CAMPUS',
            'ACTION_ADMIN_SALLE',
        ];

        foreach($classSettings as $prefix) {
            if(strpos($actionAgainst, $prefix) === 0) {
                if(strpos($actionAgainst, '_DELETE') !== false) {
                    return [Action::ACTION_ADMIN_CLASS_SETTINGS_DELETE, Action::ACTION_ADMIN_CLASS_SETTINGS_CU, Action::ACTION_ADMIN_CLASS_SETTINGS];
                }
                if(strpos($actionAgainst, '_CREATE') !== false || strpos($actionAgainst, '_UPDATE') !== false) {
                    return [Action::ACTION_ADMIN_CLASS_SETTINGS_CU, Action::ACTION_ADMIN_CLASS_SETTINGS];
                }
                return [Action::ACTION_ADMIN_CLASS_SETTINGS];
            }
        }

        if(strpos($actionAgainst, 'ACTION_STUDENT_REPORT_NOTE_') === 0) {
            return [Action::ACTION_STUDENT_REPORT_NOTE];
        }

        if(strpos($actionAgainst, 'ACTION_STUDENT_ATTESTATION_') === 0) {
            return [Action::ACTION_STUDENT_REPORT_NOTE];
        }

        if(strpos($actionAgainst, 'ACTION_STUDENT_PV_') === 0) {
            return [Action::ACTION_STUDENT_REPORT_NOTE];
        }

        if(strpos($actionAgainst, 'ACTION_STUDENT_NOTE_CREATE') === 0 || strpos($actionAgainst, 'ACTION_STUDENT_NOTE_UPDATE') === 0) {
            return [Action::ACTION_STUDENT_NOTE_CU, Action::ACTION_STUDENT_NOTE];
        }

        if(strpos($actionAgainst, 'ACTION_STUDENT_NOTE_PRINT') === 0) {
            return [Action::ACTION_STUDENT_NOTE];
        }

        if(strpos($actionAgainst, 'ACTION_STAFF_WORKER') === 0) {
            if(strpos($actionAgainst, '_DELETE') !== false) {
                return [Action::ACTION_STAFF_WORKER_DELETE, Action::ACTION_STAFF_WORKER_CU, Action::ACTION_STAFF_WORKER, Action::ACTION_STAFF];
            }
            if(strpos($actionAgainst, '_CU') !== false) {
                return [Action::ACTION_STAFF_WORKER_CU, Action::ACTION_STAFF_WORKER, Action::ACTION_STAFF];
            }
            return [Action::ACTION_STAFF_WORKER, Action::ACTION_STAFF];
        }

        if(strpos($actionAgainst, 'ACTION_STAFF_TIMETABLE') === 0) {
            if(strpos($actionAgainst, '_DELETE') !== false) {
                return [Action::ACTION_STAFF_TIMETABLE_DELETE, Action::ACTION_STAFF_TIMETABLE_CU, Action::ACTION_STAFF_TIMETABLE, Action::ACTION_STAFF];
            }
            if(strpos($actionAgainst, '_CU') !== false) {
                return [Action::ACTION_STAFF_TIMETABLE_CU, Action::ACTION_STAFF_TIMETABLE, Action::ACTION_STAFF];
            }
            return [Action::ACTION_STAFF_TIMETABLE, Action::ACTION_STAFF];
        }

        if(strpos($actionAgainst, 'ACTION_STAFF_DISCIPLINE') === 0) {
            if(strpos($actionAgainst, '_CU') !== false) {
                return [Action::ACTION_STAFF_DISCIPLINE_CU, Action::ACTION_STAFF_DISCIPLINE, Action::ACTION_STAFF];
            }
            return [Action::ACTION_STAFF_DISCIPLINE, Action::ACTION_STAFF];
        }

        if(strpos($actionAgainst, 'ACTION_STAFF_HOUR_COUNT') === 0) {
            if(strpos($actionAgainst, '_DELETE') !== false) {
                return [Action::ACTION_STAFF_HOUR_COUNT_DELETE, Action::ACTION_STAFF_HOUR_COUNT_CU, Action::ACTION_STAFF_HOUR_COUNT, Action::ACTION_STAFF];
            }
            if(strpos($actionAgainst, '_CU') !== false) {
                return [Action::ACTION_STAFF_HOUR_COUNT_CU, Action::ACTION_STAFF_HOUR_COUNT, Action::ACTION_STAFF];
            }
            return [Action::ACTION_STAFF_HOUR_COUNT, Action::ACTION_STAFF];
        }

        if(strpos($actionAgainst, 'ACTION_STAFF') === 0) {
            return [Action::ACTION_STAFF];
        }

        if(strpos($actionAgainst, 'ACTION_ADMIN_AUDIT') === 0) {
            return [Action::ACTION_ADMIN];
        }

        return [];
    }

    public function personnel() {
	    return $this->belongsTo(personnel::class, 'id_personnel');
    }

    public function campus()
    {
        return $this->belongsToMany(Campus::class, 'campus_user', 'id_user', 'id_campus')->withTimestamps();
    }

}
