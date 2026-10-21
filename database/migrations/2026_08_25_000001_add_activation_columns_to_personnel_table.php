<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddActivationColumnsToPersonnelTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('personnel', function(Blueprint $table)
        {
            if(!Schema::hasColumn('personnel','actif')){
                $table->integer('actif')->default(1);
            }

            if(!Schema::hasColumn('personnel','type')){
                $table->integer('type')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personnel', function(Blueprint $table)
        {
            if(Schema::hasColumn('personnel','actif')){
                $table->dropColumn('actif');
            }

            if(Schema::hasColumn('personnel','type')){
                $table->dropColumn('type');
            }
        });
    }
}
