<?php

return [

    /*
     * Path to the models directory.
     */
    'models_dir' => app_path('Models'),

    /*
     * Base namespace of the models.
     */
    'models_ns' => 'App\Models',

    /*
     * Path to the generated relations directory.
     */
    'relations_dir' => app_path('Models/GeneratedRelations'),

    /*
     * Base namespace of the generated relations.
     */
    'relations_ns' => 'App\Models\GeneratedRelations',

    /*
     * Tables for which we don't want to generate models.
     */
    'ignored_tables' => [
        'cache',
        'failed_jobs',
        'jobs',
        'job_batches',
        'migrations',
        'sessions',
    ],

    /*
     * Relations we don't want to be generated, specified like this:
     *
     *   'table1:table2.table1_id'  // "table1" has one/many "table2" via "table1_id"
     *   'table2.table1_id:table1'  // "table2" belongs to "table1" via "table1_id"
     *
     * For example:
     *
     *   'users:salaries.user_id'   // ignore User::salaries()
     *   'salaries.user_id:users'   // ignore Salarie::user()
     *   '*.created_by:users '      // ignore (all)::createdBy()
     *   'users:*.created_by'       // ignore User::(all)ViaCreatedBy()
     */
    'ignored_relations' => [
        '*.created_by:users',
        '*.updated_by:users',
        'users:*.created_by',
        'users:*.updated_by',
    ],

    /*
     * "has one" relations instead of "has many", specified like this:
     *
     *   'table1:table2.table1_id'  // "table1" has one "table2"
     *
     * For example:
     *
     *   'users:salaries.user_id'   // User::salarie()
     */
    'one_to_one_relations' => [
        //
    ],

    /*
     * Models grouping in sub-directories, specified like this:
     *
     *   'table'  => 'sub/directory/relative/path'
     *   '^group' => 'sub/directory/relative/path'
     *
     * Use the "^group" syntax if the group name is specified in the beginning
     * of the table name. Thus, all the targeted tables will be generated in the
     * specified sub-directory, without the group word in the model name or the
     * relations names.
     *
     * For example:
     *
     *   'users'     => 'Auth'         // App\Models\Auth\User
     *   'roles'     => 'Auth'         // App\Models\Auth\Role
     *   'role_user' => 'Auth/Pivots'  // App\Models\Auth\Pivots\RoleUser
     *
     *   // Or using prefix:
     *   '^auth'     => 'Auth'  // "auth_user"      -> App\Models\Auth\User
     *                          // "auth_role"      -> App\Models\Auth\Role
     *                          // "auth_role_user" -> App\Models\Auth\RoleUser
     */
    'groupings' => [
        //
    ],

    /*
     * Rules for converting plural to singular if not correctly done by default.
     *
     * Singularization is done on each word of the table name to get corresponding
     * model name.
     *
     * For example:
     *
     *   'ies'             => 'ie'    // all the words ending by "ies"
     *   '^sens'           => 'sens'  // only the word "sens"
     *   '^(bij|caill)oux' => '$1ou'  // only the words "bijoux" and "cailloux"
     */
    'singular_rules' => [
        '^has' => 'has',
        '^sens' => 'sens',
        '^taux' => 'taux',
        '^indices' => 'indice',
        'ies' => 'ie',
        'ixes' => 'ixe',
        'eaux' => 'eau',
        'aux' => 'al',
        'ux' => 'u',
        'sses' => 'sse',
        'ches' => 'che',
    ],

];
