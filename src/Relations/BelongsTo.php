<?php

namespace Axn\ModelsGenerator\Relations;

use Axn\ModelsGenerator\Model;

class BelongsTo extends Relation
{
    /**
     * Nom de la clé étrangère.
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * Constructeur.
     *
     * @param  Model  $parentModel
     * @param  Model  $relatedModel
     * @param  string $foreignKey
     * @return void
     */
    public function __construct(Model $parentModel, Model $relatedModel, $foreignKey)
    {
        $this->parentModel = $parentModel;
        $this->relatedModel = $relatedModel;
        $this->foreignKey = $foreignKey;

        $this->name = $this->buildName();
    }

    /**
     * Retourne le nom de la clé étrangère.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
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
            '{{foreignKey}}'   => $this->foreignKey,
        ]);
    }

    /**
     * Construit et retourne le nom de la relation.
     *
     * @return string
     */
    protected function buildName()
    {
        return camel_case(str_replace('_id', '', $this->foreignKey));
    }
}
