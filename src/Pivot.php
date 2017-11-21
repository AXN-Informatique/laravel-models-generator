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
    protected $relatedModels = [];

    /**
     * Constructeur.
     *
     * @param  string $table
     * @return void
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * Ajoute/modifie un des modèles concernés par la liaison pivot pour une clé
     * étrangère donnée.
     *
     * @param  string $foreignKey
     * @param  Model  $relatedModel
     * @return void
     */
    public function setRelatedModel($foreignKey, Model $relatedModel)
    {
        if (count($this->relatedModels) >= 2
            && !array_key_exists($foreignKey, $this->relatedModels)) {

            return;
        }

        $this->relatedModels[$foreignKey] = $relatedModel;
    }

    /**
     * Ajoute les relations n-n entre les deux modèles concernés par le pivot,
     * s'il y a bien deux modèles de renseignés.
     *
     * @return void
     */
    public function addBelongsToManyRelationsToRelatedModels()
    {
        if (count($this->relatedModels) < 2) {
            throw new \Exception(
                'Pivot table '.$this->table.' does not have enough foreign keys for determining n-n relations'
            );
        }

        $foreignKeys = array_keys($this->relatedModels);

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
