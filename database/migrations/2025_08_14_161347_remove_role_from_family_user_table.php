<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Supprimer les index éventuels qui utilisent 'role'
        // Noms d'index possibles à adapter si besoin :
        $indexes = [
            'family_user_role_index',
            'family_user_user_id_role_index',
            'family_user_family_id_role_index',
        ];

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // Sous SQLite, dropper explicitement les index avant l'altération
            foreach ($indexes as $name) {
                try {
                    DB::statement("DROP INDEX IF EXISTS {$name}");
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        } else {
            // MySQL/PostgreSQL : on peut dropper par nom via le schema builder
            Schema::table('family_user', function (Blueprint $table) use ($indexes) {
                foreach ($indexes as $name) {
                    try {
                        $table->dropIndex($name);
                    } catch (\Throwable $e) {
                        // ignore si l'index n'existe pas
                    }
                }
            });
        }

        // 2) Supprimer la colonne 'role'
        Schema::table('family_user', function (Blueprint $table) {
            if (Schema::hasColumn('family_user', 'role')) {
                $table->dropColumn('role');
            }
        });
    }

    public function down(): void
    {
        // 1) Ré-ajouter la colonne
        Schema::table('family_user', function (Blueprint $table) {
            if (!Schema::hasColumn('family_user', 'role')) {
                $table->string('role')->default('admin');
            }
        });

        // 2) (Optionnel) Recréer les index si vous en aviez besoin
        // Exemple simple :
        try {
            Schema::table('family_user', function (Blueprint $table) {
                // $table->index('role', 'family_user_role_index');
                // $table->index(['user_id','role'], 'family_user_user_id_role_index');
                // $table->index(['family_id','role'], 'family_user_family_id_role_index');
            });
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
