<?php

return [

    /*
     * Path to models directory.
     */
    'models_dir' => app_path('Models'),

    /*
     * Models namespaces
     */
    'models_ns' => 'App\Models',

    /*
     * Path to generated relations (traits) directory.
     */
    'relations_dir' => app_path('Models/GeneratedRelations'),

    /*
     * Generated relations traits namespaces.
     */
    'relations_ns' => 'App\Models\GeneratedRelations',

    /*
     * Tables for which we don't want to generate models.
     */
    'ignored_tables' => [
        'cache',
        'migrations',
        'sessions',
    ],

    /*
     * Relations we don't want to be generated, like:
     *
     *   'table1:table2.table1_id' ("table1" has one or many "table2")
     *   'table2.table1_id:table1' ("table2" belongs to "table1")
     *
     * Examples:
     *
     *   'users:salaries.user_id', // ignore User::salaries()
     *   'salaries.user_id:users', // ignore Salarie::user()
     *   '*.created_by:users',     // ignore *::createdBy()
     *   'users:*.created_by       // ignore User::*ViaCreatedBy()
     */
    'ignored_relations' => [
        '*.created_by:users',
        '*.updated_by:users',
        'users:*.created_by',
        'users:*.updated_by',
    ],

    /*
     * "has one" relations instead of "has many", like:
     *
     *   'table1:table2.table1_id'  ("table1" has one "table2")
     *
     * Examples:
     *
     *   'users:salaries.user_id', // "users" has one "salaries"
     */
    'one_to_one_relations' => [
        //
    ],

    /*
     * For grouping models in sub-directories.
     *
     * Examples:
     *
     *   'users'     => 'Auth',
     *   'roles'     => 'Auth',
     *   'role_user' => 'Auth/Pivots',
     *
     *   // Using prefix: all tables beginning with "aut_" will be generated
     *   // in the "Auth" sub-directory
     *   'aut*'      => 'Auth'
     */
    'groupings' => [
        //
    ],

    /*
     * Rules for conversion to singular when this is not done correctly by default.
     * Singularization is done on tables names to get corresponding models names.
     *
     * Examples:
     *
     *   'ies'             => 'ie',   // only on the end of the word
     *   '^sens'           => 'sens', // on the whole word
     *   '^(bij|caill)oux' => '$1ou'  // on many words
     */
    'singular_rules' => [
        '^has' => 'has',
        '^sens' => 'sens',
        '^taux' => 'taux',
        '^indices' => 'indice',
        'ies' => 'ie',
        'ixes' => 'ixe',
        'aux' => 'al',
        'ux' => 'u',
        'sses' => 'sse',
        'ches' => 'che',
    ],

];
