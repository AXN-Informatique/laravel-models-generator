<?php

namespace Axn\ModelsGenerator\Relations;

use Axn\ModelsGenerator\Model;

class MorphMany extends Relation
{
    /**
     * Nom du morph.
     *
     * @var string
     */
    protected $morphName;

    /**
     * Constructeur.
     *
     * @param  Model  $parentModel
     * @param  Model  $relatedModel
     * @param  string $morphName
     * @return void
     */
    public function __construct(Model $parentModel, Model $relatedModel, $morphName)
    {
        $this->parentModel = $parentModel;
        $this->relatedModel = $relatedModel;
        $this->morphName = $morphName;

        $this->name = $this->buildName();
    }

    /**
     * Retourne le nom du morph.
     *
     * @return void
     */
    public function getMorphName()
    {
        return $this->morphName;
    }

    /**
     * Retourne le contenu de la relation.
     *
     * @return string
     */
    protected function getContent()
    {
        return strtr($this->getRelationStubContent(), [
            '{{name}}'         => $this->name,
            '{{relatedTable}}' => $this->relatedModel->getTable(),
            '{{relatedModel}}' => $this->relatedModel->getClass(),
            '{{morphName}}'    => $this->morphName,
        ]);
    }

    /**
     * Construit et retourne le nom de la relation.
     *
     * @return string
     */
    protected function buildName()
    {
        return camel_case($this->relatedModel->getTable());
    }
}
