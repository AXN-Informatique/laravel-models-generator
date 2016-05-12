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
     * Liste des tables pivots pour la création des relations n-n.
     *
     * La relation n-n à faire est automatiquement déterminée à partir des deux
     * premières clés étrangères trouvées dans le pivot. Mais il est possible
     * d'expliciter les clés à utiliser via un tableau [pivot, fk1, fk2].
     *
     * Exemples :
     *   'role_user',
     *   ['permission_role', 'permission_id', 'role_id']
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
     * Permet de forcer le nom à donner à un modèle, si celui qui est déterminé
     * automatiquement à partir du nom de la table ne convient pas (par exemple
     * si la singularisation est erronée, comme 'sorties' qui devient 'Sorty').
     *
     * Exemple :
     *   'sorties' => 'Sortie'
     */
    'forced_names' => [
        //
    ],

    /*
     * Permet de regrouper dans des sous-dossiers les modèles concernés par une
     * même thématique.
     *
     * Exemples :
     *   'users'     => 'Auth',
     *   'roles'     => 'Auth',
     *   'role_user' => 'Auth/Pivots'
     */
    'groups' => [
        //
    ],

];
