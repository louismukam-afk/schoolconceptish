<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RestoreLegacyPaymentCampuses extends Migration {
    public function up()
    {
        if (DB::connection()->getDriverName() !== 'pgsql' ||
            !Schema::hasColumn('payement', 'id_campus') ||
            !Schema::hasColumn('inscription', 'id_campus')) {
            return;
        }

        DB::transaction(function () {
            // Registration fees have a direct inscription link.
            DB::statement("UPDATE payement AS p
                SET id_campus = i.id_campus
                FROM inscription AS i
                WHERE p.id_campus IS NULL
                  AND p.id_inscription = i.id
                  AND p.id_eleve = i.id_eleve
                  AND p.annee_scolaire = i.annee_scolaire
                  AND i.id_campus IS NOT NULL");

            // Other legacy payments use 0 instead of an inscription ID.
            // Only infer a campus when every matching registration agrees.
            DB::statement("UPDATE payement AS p
                SET id_campus = i.id_campus
                FROM (
                    SELECT id_eleve, annee_scolaire, MIN(id_campus) AS id_campus
                    FROM inscription
                    GROUP BY id_eleve, annee_scolaire
                    HAVING COUNT(*) = COUNT(id_campus)
                       AND COUNT(DISTINCT id_campus) = 1
                ) AS i
                WHERE p.id_campus IS NULL
                  AND (p.id_inscription IS NULL OR p.id_inscription = 0)
                  AND p.id_eleve = i.id_eleve
                  AND p.annee_scolaire = i.annee_scolaire");
        });
    }

    public function down()
    {
        // Preserve reconciled campus assignments and financial history.
    }
}
