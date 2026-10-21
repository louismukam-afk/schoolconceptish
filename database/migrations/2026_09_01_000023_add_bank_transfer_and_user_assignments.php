<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddBankTransferAndUserAssignments extends Migration {
    public function up()
    {
        if (Schema::hasTable('mouvements_financiers')) {
            Schema::table('mouvements_financiers', function(Blueprint $table) {
                if (!Schema::hasColumn('mouvements_financiers', 'id_banque_source')) {
                    $table->integer('id_banque_source')->nullable();
                }
                if (!Schema::hasColumn('mouvements_financiers', 'id_banque_destination')) {
                    $table->integer('id_banque_destination')->nullable();
                }
            });
        }

        if (!Schema::hasTable('caisse_user')) {
            Schema::create('caisse_user', function(Blueprint $table) {
                $table->increments('id');
                $table->integer('id_caisse')->unsigned();
                $table->integer('id_user')->unsigned();
                $table->integer('id_creator')->nullable();
                $table->integer('id_last_editor')->nullable();
                $table->timestamps();
                $table->unique(array('id_caisse', 'id_user'));
            });
        }

        if (!Schema::hasTable('banque_user')) {
            Schema::create('banque_user', function(Blueprint $table) {
                $table->increments('id');
                $table->integer('id_banque')->unsigned();
                $table->integer('id_user')->unsigned();
                $table->integer('id_creator')->nullable();
                $table->integer('id_last_editor')->nullable();
                $table->timestamps();
                $table->unique(array('id_banque', 'id_user'));
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('caisse_user');
        Schema::dropIfExists('banque_user');

        if (Schema::hasTable('mouvements_financiers')) {
            Schema::table('mouvements_financiers', function(Blueprint $table) {
                if (Schema::hasColumn('mouvements_financiers', 'id_banque_source')) {
                    $table->dropColumn('id_banque_source');
                }
                if (Schema::hasColumn('mouvements_financiers', 'id_banque_destination')) {
                    $table->dropColumn('id_banque_destination');
                }
            });
        }
    }
}
