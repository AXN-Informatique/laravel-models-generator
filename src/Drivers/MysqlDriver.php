<?php

namespace Axn\ModelsGenerator\Drivers;

use PDO;

class MysqlDriver implements Driver
{
    /**
     * Instance PDO pour la connexion à la base de données.
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * Liste des instructions SQL de création de table, par table.
     *
     * @var array[string]
     */
    protected $sqlCreateTable = [];

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
     * @param  string $table
     * @return string
     */
    public function getSqlCreateTable($table)
    {
        if (empty($this->sqlCreateTable[$table])) {
            $query = $this->pdo->query("SHOW CREATE TABLE $table");

            $this->sqlCreateTable[$table] = $query->fetchColumn(1);
        }

        return $this->sqlCreateTable[$table];
    }

    /**
     * Récupère les contraintes d'une table à partir du code SQL de sa création.
     *
     * @param  string $table
     * @return array[string]
     */
    public function getTableConstraintsInfo($table)
    {
        preg_match_all(
            '/CONSTRAINT `\w+` FOREIGN KEY \(`(.+)`\) REFERENCES `(\w+)` \(`.+`\)/Us',
            $this->getSqlCreateTable($table),
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
