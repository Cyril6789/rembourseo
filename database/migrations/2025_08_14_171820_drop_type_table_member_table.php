<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $driver = DB::getDriverName();

        // --- 1) Drop des index qui référencent 'type' ---
        if ($driver === 'sqlite') {
            // On détecte dynamiquement tous les index de 'members' dont la définition contient 'type'
            $indexes = DB::select("
                SELECT name, sql
                FROM sqlite_master
                WHERE type = 'index' AND tbl_name = 'members'
            ");

            foreach ($indexes as $idx) {
                $sql = $idx->sql ?? '';
                if ($sql && stripos($sql, ' type ') !== false) {
                    // Index explicitement créé (a un SQL)
                    DB::statement("DROP INDEX IF EXISTS {$idx->name}");
                } else {
                    // Certains index auto-générés peuvent ne pas avoir de SQL stocké
                    // On tente quand même de les drop si leur nom laisse deviner qu'ils incluent 'type'
                    if (stripos($idx->name, 'type') !== false) {
                        DB::statement("DROP INDEX IF EXISTS {$idx->name}");
                    }
                }
            }

            // --- 2) Drop de la colonne ---
            if (Schema::hasColumn('members', 'type')) {
                Schema::table('members', function (Blueprint $table) {
                    $table->dropColumn('type');
                });
            }
        } else {
            // MySQL / PostgreSQL
            // D’abord essayer de dropper quelques noms d’index “classiques”.
            // Ajuste la liste si tu connais tes noms exacts.
            $possibleIndexNames = [
                'members_type_index',
                'members_type_unique',
                'members_family_id_type_last_name_index',
                'members_family_id_type_index',
            ];

            Schema::table('members', function (Blueprint $table) use ($possibleIndexNames) {
                foreach ($possibleIndexNames as $name) {
                    try {
                        $table->dropIndex($name);       // index normal
                    } catch (\Throwable $e) {}
                    try {
                        $table->dropUnique($name);      // unique index
                    } catch (\Throwable $e) {}
                }
            });

            // Puis drop de la colonne
            if (Schema::hasColumn('members', 'type')) {
                Schema::table('members', function (Blueprint $table) {
                    $table->dropColumn('type');
                });
            }
        }
    }

    public function down(): void
    {
        // On rétablit la colonne 'type' (ajuste le type et les valeurs par défaut si nécessaire)
        if (!Schema::hasColumn('members', 'type')) {
            Schema::table('members', function (Blueprint $table) {
                $table->string('type')->nullable()->after('family_id');
            });
        }

        // (Optionnel) Recréer l’index composite qui existait chez toi
        // d’après le message d’erreur : members_family_id_type_last_name_index
        try {
            Schema::table('members', function (Blueprint $table) {
                $table->index(['family_id', 'type', 'last_name'], 'members_family_id_type_last_name_index');
            });
        } catch (\Throwable $e) {
            // ignore si l'index existe déjà ou si la colonne n'existe pas
        }
    }
};
