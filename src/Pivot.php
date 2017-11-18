<?php

namespace Axn\ModelsGenerator;

class Pivot
{
    /**
     * Nom de la table pivot.
     *
     * @var string
     */
    protected $table;

    /**
     * Modèles concernés par la liaison pivot, par clé.
     *
     * @var array[Model]
     */
    protected $models;

    /**
     * Constructeur.
     *
     * @param  string      $table
     * @param  string|null $foreignKey1
     * @param  string|null $foreignKey2
     * @return void
     */
    public function __construct($table, $foreignKey1 = null, $foreignKey2 = null)
    {
        $this->table = $table;
        $this->models = [];

        if ($foreignKey1) {
            $this->models[$foreignKey1] = null;
        }

        if ($foreignKey2) {
            $this->models[$foreignKey2] = null;
        }
    }

    /**
     * Ajoute/modifie le modèle associé à une clé.
     *
     * @param  string $foreignKey
     * @param  Model  $model
     * @return void
     */
    public function setModel($foreignKey, Model $model)
    {
        $this->models[$foreignKey] = $model;
    }

    /**
     * Ajoute les relations n-n entre les 2 modèles concernés par le pivot,
     * s'il y a bien 2 modèles de renseignés.
     *
     * @return void
     */
    public function addBelongsToManyRelationsToModels()
    {
        $foreignKeys = array_keys($this->models);

        if (count($foreignKeys) < 2
            || empty($this->models[$foreignKeys[0]])
            || empty($this->models[$foreignKeys[1]])) {

            throw new \Exception(
                'Not enough keys in pivot table '.$this->table.' for creating many-to-many relations'
            );
        }

        // Relation n-n du modèle A vers le modèle B
        $this->models[$foreignKeys[0]]->belongsToMany(
            $this->models[$foreignKeys[1]],
            $this->table,
            $foreignKeys[0],
            $foreignKeys[1]
        );

        // Relation n-n du modèle B vers le modèle A
        $this->models[$foreignKeys[1]]->belongsToMany(
            $this->models[$foreignKeys[0]],
            $this->table,
            $foreignKeys[1],
            $foreignKeys[0]
        );
    }
}
