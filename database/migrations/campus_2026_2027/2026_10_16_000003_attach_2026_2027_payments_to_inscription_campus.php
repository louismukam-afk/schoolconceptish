<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Attach20262027PaymentsToInscriptionCampus extends Migration {
    public function up()
    {
        if (DB::connection()->getDriverName() !== 'pgsql' ||
            !Schema::hasColumn('payement', 'id_campus') ||
            !Schema::hasColumn('inscription', 'id_campus')) {
            return;
        }

        DB::transaction(function () {
            DB::statement("UPDATE payement AS p SET id_campus = i.id_campus
                FROM inscription AS i
                WHERE (p.id_campus IS NULL OR p.id_campus = 0)
                  AND replace(p.annee_scolaire, ' ', '') = '2026-2027'
                  AND replace(i.annee_scolaire, ' ', '') = '2026-2027'
                  AND p.id_inscription = i.id AND p.id_eleve = i.id_eleve
                  AND i.id_campus > 0");

            DB::statement("UPDATE payement AS p SET id_campus = i.id_campus
                FROM (
                    SELECT id_eleve, MIN(id_campus) AS id_campus
                    FROM inscription
                    WHERE replace(annee_scolaire, ' ', '') = '2026-2027'
                    GROUP BY id_eleve
                    HAVING COUNT(*) = COUNT(id_campus)
                       AND MIN(id_campus) > 0 AND COUNT(DISTINCT id_campus) = 1
                ) AS i
                WHERE (p.id_campus IS NULL OR p.id_campus = 0)
                  AND replace(p.annee_scolaire, ' ', '') = '2026-2027'
                  AND (p.id_inscription IS NULL OR p.id_inscription = 0)
                  AND p.id_eleve = i.id_eleve");

            if (Schema::hasColumn('mouvements_financiers', 'id_campus')) {
                DB::statement("UPDATE mouvements_financiers AS m SET id_campus = p.id_campus
                    FROM payement AS p
                    WHERE (m.id_campus IS NULL OR m.id_campus = 0)
                      AND m.id_payement = p.id AND p.id_campus > 0
                      AND replace(p.annee_scolaire, ' ', '') = '2026-2027'");
            }
        });
    }

    public function down()
    {
        // A rollback must not discard reconciled financial history.
    }
}
