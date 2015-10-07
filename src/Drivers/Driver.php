<?php

namespace Axn\ModelsGenerator\Drivers;

interface Driver
{
    /**
     * Récupère et retourne les noms des tables de la BDD.
     *
     * @return array[string]
     */
    public function getTablesNames();

    /**
     * Retourne le code SQL pour la création de la table.
     *
     * @param  string $tableName
     * @return string
     */
    public function getSqlCreateTable($tableName);

    /**
     * Récupère les contraintes d'une table à partir du code SQL de sa création.
     *
     * @param  string
     * @return array[string]
     */
    public function getTableConstraintsInfo($tableName);
}
