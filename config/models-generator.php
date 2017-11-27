<?php

return [

    /*
     * Chemin vers le répertoire où doivent être générés les modèles.
     */
    'models_dir' => app_path('Models'),

    /*
     * Espace de nom des modèles.
     */
    'models_ns' => 'App\Models',

    /*
     * Liste des tables pour lesquelles on ne souhaite pas générer de modèle.
     */
    'ignored_tables' => [
        'cache',
        'migrations',
        'sessions',
    ],

    /*
     * Liste des tables pivots pour créer les relations n-n ("belongs to many").
     *
     * La relation n-n à faire est déterminée à partir des deux premières clés
     * étrangères trouvées dans le pivot. Les tables dont le nom contient le
     * mot clé "_has_" seront automatiquement reconnues comme étant des pivots
     * et vous n'avez donc pas besoin de les ajouter à cette liste.
     */
    'pivot_tables' => [
        //
    ],

    /*
     * Liste des relations polymorphiques sous la forme :
     *   'morph_table.morph_name' => ['table1', 'table2', ...]
     *
     * Exemple :
     *   'photos.imageable' => ['staff', 'orders']
     */
    'polymorphic_relations' => [
        //
    ],

    /*
     * Liste des relations 1-1 ("has one" ou "morph one") sous la forme :
     *   'table1:table2.table1_id'  ("table1" has one "table2")
     *   'table1:table2.morph_name' ("table1" morph one "table2")
     *
     * Exemples :
     *   'users:salaries.user_id', // "users" has one "salaries"
     *   'staff:photos.imageable'  // "staff" morph one "photos"
     */
    'one_to_one_relations' => [
        //
    ],

    /*
     * Liste des relations que l'on ne souhaite pas générer, sous la forme :
     *   'table1:table2.table1_id' ("table1" has one or many "table2")
     *   'table2.table1_id:table1' ("table2" belongs to "table1")
     *
     * Exemples :
     *   'users:salaries.user_id', // ignorer "users" has one/many "salaries"
     *   'salaries.user_id:users'  // ignorer "salaries" belongs to "users"
     */
    'ignored_relations' => [
        //
    ],

    /*
     * Permet de regrouper des modèles dans des sous-dossiers.
     *
     * Exemples :
     *   'users'     => 'Auth',
     *   'roles'     => 'Auth',
     *   'role_user' => 'Auth/Pivots'
     */
    'groupings' => [
        //
    ],

    /*
     * Règles pour former le singulier lorsque celui-ci n'est pas correctement
     * formé par défaut (fonction "str_singular" de Laravel). La singularisation
     * est effectuée sur les noms de tables pour obtenir les noms de modèles.
     *
     * Exemples :
     *   'ies'             => 'ie',   // uniquement la fin du mot
     *   '^sens'           => 'sens', // mot entier
     *   '^(bij|caill)oux' => '$1ou'  // plusieurs mots
     */
    'singular_rules' => [
        'ies' => 'ie',
        'aux' => 'al',
        'ux' => 'u',
        'sses' => 'sse',
        'ches' => 'che',
    ],

];
