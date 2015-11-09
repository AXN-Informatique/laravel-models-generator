<?php

namespace Axn\ModelsGenerator\Drivers;

use PDO;

class MysqlDriver implements Driver
{
    /**
     * Liste des codes SQL de création de table, par table.
     *
     * @var array[string]
     */
    private static $sqlCreateTable = [];

    /**
     * Instance PDO pour la connexion à la base de données.
     *
     * @var PDO
     */
    private $pdo;

    /**
     * Constructeur.
     *
     * @param  PDO $pdo
     * @return void
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère et retourne les noms des tables de la BDD.
     *
     * @return array[string]
     */
    public function getTablesNames()
    {
        $query = $this->pdo->query('SHOW TABLES');

        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Retourne le code SQL pour la création de la table.
     *
     * @param  string $tableName
     * @return string
     */
    public function getSqlCreateTable($tableName)
    {
        if (empty(static::$sqlCreateTable[$tableName])) {
            $query = $this->pdo->query("SHOW CREATE TABLE $tableName");

            static::$sqlCreateTable[$tableName] = $query->fetchColumn(1);
        }

        return static::$sqlCreateTable[$tableName];
    }

    /**
     * Récupère les contraintes d'une table à partir du code SQL de sa création.
     *
     * @param  string
     * @return array[string]
     */
    public function getTableConstraintsInfo($tableName)
    {
        preg_match_all(
            '/CONSTRAINT `\w+` FOREIGN KEY \(`(.+)`\) REFERENCES `(\w+)` \(`.+`\)/Us',
            $this->getSqlCreateTable($tableName),
            $matches
        );

        $constraintsInfo = [];

        foreach ($matches[0] as $index => $constraint) {
            $constraintsInfo[] = [
                'foreignKey'   => $matches[1][$index],
                'relatedTable' => $matches[2][$index]
            ];
        }

        return $constraintsInfo;
    }
}
