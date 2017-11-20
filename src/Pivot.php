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
     * Contient les instances des deux modèles concernés par la liaison pivot.
     *
     * @var array[Model]
     */
    protected $relatedModels;

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
        $this->relatedModels = [];

        if ($foreignKey1) {
            $this->relatedModels[$foreignKey1] = null;
        }

        if ($foreignKey2) {
            $this->relatedModels[$foreignKey2] = null;
        }
    }

    /**
     * Ajoute/modifie le modèle concerné par la liaison pivot pour une des clés
     * étrangères donnée.
     *
     * @param  string $foreignKey
     * @param  Model  $relatedModels
     * @return void
     */
    public function setRelatedModel($foreignKey, Model $relatedModels)
    {
        $this->relatedModels[$foreignKey] = $relatedModels;
    }

    /**
     * Ajoute les relations n-n entre les deux modèles concernés par le pivot,
     * s'il y a bien au moins deux modèles de renseignés.
     *
     * @return void
     */
    public function addBelongsToManyRelationsToRelatedModels()
    {
        $foreignKeys = array_keys($this->relatedModels);

        if (count($foreignKeys) < 2
            || empty($this->relatedModels[$foreignKeys[0]])
            || empty($this->relatedModels[$foreignKeys[1]])) {

            throw new \Exception(
                'Not enough keys in pivot table '.$this->table.' for creating many-to-many relations'
            );
        }

        // Relation n-n du modèle A vers le modèle B
        $this->relatedModels[$foreignKeys[0]]->belongsToMany(
            $this->relatedModels[$foreignKeys[1]],
            $this->table,
            $foreignKeys[0],
            $foreignKeys[1]
        );

        // Relation n-n du modèle B vers le modèle A
        $this->relatedModels[$foreignKeys[1]]->belongsToMany(
            $this->relatedModels[$foreignKeys[0]],
            $this->table,
            $foreignKeys[1],
            $foreignKeys[0]
        );
    }
}
