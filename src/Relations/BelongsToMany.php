<?php

namespace Axn\ModelsGenerator\Relations;

use Axn\ModelsGenerator\Model;

class BelongsToMany extends Relation
{
    /**
     * Nom de la table pivot.
     *
     * @var string
     */
    protected $pivotTable;

    /**
     * Nom de la clé étrangère faisant le lien entre la table A (modèle parent)
     * et la table pivot.
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * Nom de la clé étrangère faisant le lien entre table B (modèle associé)
     * et la table pivot.
     *
     * @var string
     */
    protected $otherKey;

    /**
     * Constructeur.
     *
     * @param  Model  $parentModel
     * @param  Model  $relatedModel
     * @param  string $pivotTable
     * @param  string $foreignKey
     * @param  string $otherKey
     * @return void
     */
    public function __construct(Model $parentModel, Model $relatedModel, $pivotTable, $foreignKey, $otherKey)
    {
        $this->parentModel = $parentModel;
        $this->relatedModel = $relatedModel;
        $this->pivotTable = $pivotTable;
        $this->foreignKey = $foreignKey;
        $this->otherKey = $otherKey;

        $this->name = $this->buildName();
    }

    /**
     * Retourne le nom de la table pivot.
     *
     * @return string
     */
    public function getPivotTable()
    {
        return $this->pivotTable;
    }

    /**
     * Retourne le nom de la clé étrangère concernant le modèle parent.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * Retourne le nom de la clé étrangère concernant le modèle associé.
     *
     * @return string
     */
    public function getOtherKey()
    {
        return $this->otherKey;
    }

    /**
     * Retourne le contenu de la relation.
     *
     * @return string
     */
    public function getContent()
    {
        return strtr($this->getRelationStubContent(), [
            '{{name}}'         => $this->name,
            '{{relatedTable}}' => $this->relatedModel->getTable(),
            '{{relatedModel}}' => $this->relatedModel->getClass(),
            '{{pivotTable}}'   => $this->pivotTable,
            '{{foreignKey}}'   => $this->foreignKey,
            '{{otherKey}}'     => $this->otherKey,
        ]);
    }

    /**
     * Construit et retourne le nom de la relation.
     *
     * @return string
     */
    protected function buildName()
    {
        return camel_case($this->relatedModel->getTableWithoutPrefix())
             . $this->getNamePrecision();
    }

    /**
     * Retourne la précision à concaténer au nom de la relation lorsque le nom
     * de la table pivot n'est pas standard.
     *
     * @return string
     */
    protected function getNamePrecision()
    {
        $snakeParentModelName = snake_case($this->parentModel->getName());
        $snakeRelatedModelName = snake_case($this->relatedModel->getName());

        $recognizedPivotTableNames = [
            $snakeParentModelName.'_has_'.$this->relatedModel->getTable(),
            $snakeRelatedModelName.'_has_'.$this->parentModel->getTable(),
            $snakeParentModelName.'_'.$snakeRelatedModelName,
            $snakeRelatedModelName.'_'.$snakeParentModelName,
        ];

        foreach ($recognizedPivotTableNames as $recognizedName) {
            if ($this->pivotTable === $recognizedName) {
                return '';
            }
        }

        return 'Via'.studly_case($this->pivotTable);
    }
}
