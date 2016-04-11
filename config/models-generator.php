<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Répertoire et namespace de base des modèles.
    |--------------------------------------------------------------------------
    */

    'models_dir' => app_path('Models'),

    'models_ns' => 'App\Models',

    /*
    |--------------------------------------------------------------------------
    | Tables à ignorer.
    |--------------------------------------------------------------------------
    |
    | Liste des tables pour lesquelles on ne souhaite pas générer de modèle.
    |
    */

    'ignored_tables' => [
        'migrations',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tables pivots.
    |--------------------------------------------------------------------------
    |
    | Liste des tables pivots pour la création des relations n-n.
    |
    | Vous pouvez soit renseigner uniquement le nom de la table pivot (dans ce
    | cas, la relation n-n à faire sera déterminée à partir des deux premières
    | clés étrangères trouvées dans le pivot), soit renseigner explicitement
    | les clés via un tableau de la forme : ['table_pivot', 'fk1', 'fk2'].
    |
    | Exemple :
    |     'role_user'
    |   ou avec précision des clés :
    |     ['role_user', 'role_id', 'user_id']
    |
    */

    'pivot_tables' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Relations polymorphiques.
    |--------------------------------------------------------------------------
    |
    | Liste des relations polymorphiques, par table, sous la forme :
    | 'nom_table' => ['nom_morph' => [liste_tables_liées]].
    |
    | Exemple :
    |   'photos' => [
    |     'imageable' => ['staff', 'orders']
    |   ]
    |
    */

    'polymorphic_relations' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Relations 1-1.
    |--------------------------------------------------------------------------
    |
    | Liste des relations 1-1 (classiques ou polymorphiques) sous la forme :
    | 'nom_table' => [liste_tables(.nom_fk)] (mettre nom_morph à la place de
    | nom_fk si relation polymorphique).
    |
    | Exemple (staff has one user and morph one photo) :
    |     'staff' => ['users', 'photos']
    |   ou avec précision fk/morph :
    |     'staff' => ['users.staff_id', 'photos.imageable']
    |
    */

    'one_to_one_relations' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Relations à ignorer.
    |--------------------------------------------------------------------------
    |
    | Liste des relations "belongs to" et "has one or many" que l'on ne souhaite
    | pas générer, sous la forme : 'nom_table' => [liste_tables(.nom_fk)].
    |
    | Exemple :
    |     'posts' => ['comment_post', 'users']
    |   ou avec précision fk :
    |     'posts' => ['comment_post.post_id', 'users.user_id']
    |
    */

    'ignored_relations' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Noms forcés des modèles.
    |--------------------------------------------------------------------------
    |
    | Permet de forcer le nom à donner à un modèle, si celui qui est déterminé
    | automatiquement à partir du nom de la table ne convient pas (par exemple
    | si la singularisation est erronée, comme 'sorties' qui devient 'Sorty').
    |
    | Exemple :
    |   'sorties' => 'Sortie'
    |
    */

    'forced_names' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Groupes.
    |--------------------------------------------------------------------------
    |
    | Permet de regrouper dans des sous-dossiers les modèles concernés par une
    | même thématique.
    |
    | Exemple :
    |   'users' => 'Auth',
    |   'roles' => 'Auth',
    |   'role_user' => 'Auth/Pivots'
    |
    */

    'groups' => [
        //
    ],

];
