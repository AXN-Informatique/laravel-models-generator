<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tables pivot.
    |--------------------------------------------------------------------------
    |
    | Liste des tables pivot. En effet, le générateur peut détecter les relations
    | belongsTo et hasMany grâce aux clés étrangères, mais est incapable de deviner
    | quand un pivot entre en jeu (belongsToMany).
    |
    | Il faut donc lui préciser ici quelles sont les tables pivot. Le générateur
    | utilisera alors les clés des relations belongsTo de la table pivot pour
    | retrouver les tables à lier.
    |
    */

    'pivot_tables' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Tables polymorphiques.
    |--------------------------------------------------------------------------
    |
    | Liste des tables polymorphiques avec les informations sur les relations.
    |
    | Exemple :
    |   'photos' => [
    |     'imageable' => ['staff', 'orders']
    |   ]
    |
    */

    'polymorphic_tables' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Noms forcés pour les classes des modèles.
    |--------------------------------------------------------------------------
    |
    | Les noms des modèles sont automatiquement déterminés à partir des noms des
    | tables (nom modèle = nom table au singulier et studly case). Il arrive
    | cependant que le nom soit mal déterminé si celui-ci n'est pas anglais.
    | Par exemple, le mot "sorties" singularisé devient "sorty" et non "sortie".
    | Il est donc possible de forcer des nommages ici.
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
    | Permet de regrouper les fichiers concernés par une même thématique.
    |
    | Exemple :
    |   'Params'    => ['table1', 'table2', 'table3']
    |   'Toto\Titi' => ['table4', 'table5']
    |
    */

    'groups' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Nom du groupe pour les tables pivot.
    |--------------------------------------------------------------------------
    |
    | Revient au même que de créer un groupe 'Pivots' et y mettre la liste des
    | tables du paramètre 'pivot_tables', mais ce paramètre permet justement
    | d'éviter cette duplication. Commenter ou mettre une chaine vide si vous
    | ne souhaitez pas mettre les tables pivot dans un groupe.
    |
    */

    //'pivot_tables_group' => 'Pivots',

    /*
    |--------------------------------------------------------------------------
    | Chemins vers le répertoire des templates.
    |--------------------------------------------------------------------------
    |
    | La génération s'effectue à partir de templates. Le générateur a des templates
    | de définis par défaut, mais il est possible de lui demander d'utiliser des
    | templates personnalisés à la place en les plaçant dans ce répertoire.
    |
    */

    'templates_dir' => base_path().'/templates',

    /*
    |--------------------------------------------------------------------------
    | Modèles
    |--------------------------------------------------------------------------
    |
    | Configuration pour la génération des modèles.
    | Mettre NULL ou supprimer si génération des modèles non souhaitée.
    |
    */

    'models' => [
        'dir' => app_path('Models'), // Où seront générés les modèles
        'ns'  => 'App\Models', // Namespace des modèles

        // Tables à ignorer (les repositories, contrats et façades ne seront pas non plus générés)
        'ignored_tables' => [
            'migrations'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Repositories
    |--------------------------------------------------------------------------
    |
    | Configuration pour la génération des implémentation Eloquent des repositories.
    | Mettre NULL ou supprimer si génération des repositories non souhaitée.
    |
    */

    'repositories' => [
        'dir' => app_path('Repositories'), // Où seront générés les repositories
        'ns'  => 'App\Repositories', // Namespace des repositories

        // Tables à ignorer (les contrats et façades ne seront pas non plus générés)
        'ignored_tables' => [
            //
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Contrats
    |--------------------------------------------------------------------------
    |
    | Configuration pour la génération des interfaces des repositories.
    | Mettre NULL ou supprimer si génération des contrats non souhaitée.
    |
    */

    'contracts' => [
        'dir' => app_path('Contracts/Repositories'), // Où seront générés les contrats
        'ns'  => 'App\Contracts\Repositories', // Namespace des contrats
    ],

    /*
    |--------------------------------------------------------------------------
    | Façades
    |--------------------------------------------------------------------------
    |
    | Configuration pour la génération des façades des repositories.
    | Mettre NULL ou supprimer si génération des façades non souhaitée.
    |
    */

    'facades' => [
        'dir' => app_path('Facades/Repositories'), // Où seront générées les façades
        'ns'  => 'App\Facades\Repositories', // Namespace des façades
    ],

];
