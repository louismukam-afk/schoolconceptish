<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAccessControlTables extends Migration {
    public function up()
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'is_super_admin')) {
            Schema::table('users', function(Blueprint $table) {
                $table->boolean('is_super_admin')->default(false);
            });

            if (Schema::hasTable('actions')) {
                $adminIds = DB::table('actions')->where('action_name', '=', 'ACTION_ALL')->lists('id_user');
                if (count($adminIds) > 0) {
                    DB::table('users')->whereIn('id', $adminIds)->update(['is_super_admin' => true]);
                }
            }
        }

        if (!Schema::hasTable('access_roles')) {
            Schema::create('access_roles', function(Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('access_role_actions')) {
            Schema::create('access_role_actions', function(Blueprint $table) {
                $table->increments('id');
                $table->integer('id_access_role')->unsigned();
                $table->string('action_name');
                $table->timestamps();
                $table->unique(array('id_access_role', 'action_name'), 'access_role_action_unique');
            });
        }

        if (!Schema::hasTable('access_role_user')) {
            Schema::create('access_role_user', function(Blueprint $table) {
                $table->increments('id');
                $table->integer('id_access_role')->unsigned();
                $table->integer('id_user')->unsigned();
                $table->timestamps();
                $table->unique(array('id_access_role', 'id_user'), 'access_role_user_unique');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('access_role_user');
        Schema::dropIfExists('access_role_actions');
        Schema::dropIfExists('access_roles');

        // On conserve users.is_super_admin au rollback pour eviter de supprimer
        // un champ de securite deja present avant cette migration.
    }
}
