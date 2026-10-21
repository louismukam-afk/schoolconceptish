<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutreFromEleve extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('eleve', function(Blueprint $table)
		{
			if (!Schema::hasColumn('eleve', 'telephone_wathsapp_eleve')) {
				$table->string('telephone_wathsapp_eleve')->default(0);
			}
			if (!Schema::hasColumn('eleve', 'moyenne_generale')) {
				$table->float('moyenne_generale')->default(0);
			}
			if (!Schema::hasColumn('eleve', 'serie')) {
				$table->string('serie')->nullable();
			}
			if (!Schema::hasColumn('eleve', 'localisation_ecole')) {
				$table->string('localisation_ecole')->nullable();
			}
			if (!Schema::hasColumn('eleve', 'source_financement')) {
				$table->string('source_financement')->nullable();
			}
			if (!Schema::hasColumn('eleve', 'canal_decouvert')) {
				$table->string('canal_decouvert')->nullable();
			}
			if (!Schema::hasColumn('eleve', 'site_internet')) {
				$table->string('site_internet')->nullable();
			}
			if (!Schema::hasColumn('eleve', 'abonnement')) {
				$table->string('abonnement')->nullable();
			}
			if (!Schema::hasColumn('eleve', 'telephone_wathsapp_pere')) {
				$table->string('telephone_wathsapp_pere')->nullable();
			}
			if (!Schema::hasColumn('eleve', 'telephone_wathsapp_mere')) {
				$table->string('telephone_wathsapp_mere')->nullable();
			}
			if (!Schema::hasColumn('eleve', 'telephone_wathsapp_tuteur')) {
				$table->string('telephone_wathsapp_tuteur')->nullable();
			}
			if (!Schema::hasColumn('eleve', 'telephone_wathsapp_autre')) {
				$table->string('telephone_wathsapp_autre')->nullable();
			}
			if (!Schema::hasColumn('eleve', 'abonnement_valide')) {
				$table->string('abonnement_valide')->nullable();
			}
			if (!Schema::hasColumn('eleve', 'chaine')) {
				$table->string('chaine')->nullable();
			}
			if (!Schema::hasColumn('eleve', 'numero_conseille')) {
				$table->string('numero_conseille')->nullable();
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
		Schema::table('eleve', function(Blueprint $table)
		{
			if (Schema::hasColumn('eleve', 'telephone_wathsapp_eleve')) {
				$table->dropColumn('telephone_wathsapp_eleve');
			}
			if (Schema::hasColumn('eleve', 'moyenne_generale')) {
				$table->dropColumn('moyenne_generale');
			}
			if (Schema::hasColumn('eleve', 'serie')) {
				$table->dropColumn('serie');
			}
			if (Schema::hasColumn('eleve', 'localisation_ecole')) {
				$table->dropColumn('localisation_ecole');
			}
			if (Schema::hasColumn('eleve', 'source_financement')) {
				$table->dropColumn('source_financement');
			}
			if (Schema::hasColumn('eleve', 'canal_decouvert')) {
				$table->dropColumn('canal_decouvert');
			}
			if (Schema::hasColumn('eleve', 'site_internet')) {
				$table->dropColumn('site_internet');
			}
			if (Schema::hasColumn('eleve', 'abonnement')) {
				$table->dropColumn('abonnement');
			}
			if (Schema::hasColumn('eleve', 'telephone_wathsapp_pere')) {
				$table->dropColumn('telephone_wathsapp_pere');
			}
			if (Schema::hasColumn('eleve', 'telephone_wathsapp_mere')) {
				$table->dropColumn('telephone_wathsapp_mere');
			}
			if (Schema::hasColumn('eleve', 'telephone_wathsapp_tuteur')) {
				$table->dropColumn('telephone_wathsapp_tuteur');
			}
			if (Schema::hasColumn('eleve', 'telephone_wathsapp_autre')) {
				$table->dropColumn('telephone_wathsapp_autre');
			}
			if (Schema::hasColumn('eleve', 'abonnement_valide')) {
				$table->dropColumn('abonnement_valide');
			}
			if (Schema::hasColumn('eleve', 'chaine')) {
				$table->dropColumn('chaine');
			}
			if (Schema::hasColumn('eleve', 'numero_conseille')) {
				$table->dropColumn('numero_conseille');
			}
		});
	}

}
