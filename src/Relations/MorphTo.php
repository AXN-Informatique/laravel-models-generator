<?php

namespace Axn\ModelsGenerator\Relations;

use Axn\ModelsGenerator\Model;

class MorphTo extends Relation
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
     * @param  Model $parentModel
     * @param  string $morphName
     * @return void
     */
    public function __construct(Model $parentModel, $morphName)
    {
        $this->parentModel = $parentModel;
        $this->morphName = $morphName;
        
        $this->name = $morphName;
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
            '{{name}}' => $this->getName(),
        ]);
    }
}
