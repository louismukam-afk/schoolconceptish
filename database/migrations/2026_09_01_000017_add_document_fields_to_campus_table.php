<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDocumentFieldsToCampusTable extends Migration {
    public function up()
    {
        Schema::table('campus', function(Blueprint $table) {
            $table->text('autorisation_ouverture')->nullable();
            $table->text('arrete_creation')->nullable();
            $table->string('boite_postale')->nullable();
        });
    }

    public function down()
    {
        Schema::table('campus', function(Blueprint $table) {
            $table->dropColumn(array('autorisation_ouverture', 'arrete_creation', 'boite_postale'));
        });
    }
}
