<?php

namespace Axn\ModelsGenerator\Relations;

use Axn\ModelsGenerator\Model;
use Axn\ModelsGenerator\Traits\HasStub;

abstract class Relation
{
    use HasStub;

    /**
     * Nom de la relation.
     *
     * @var string
     */
    protected $name;

    /**
     * Instance du modèle parent.
     *
     * @var Model
     */
    protected $parentModel;

    /**
     * Instance du modèle associé.
     *
     * @var Model
     */
    protected $relatedModel;

    /**
     * Retourne le nom de la relation.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Retourne l'instance du modèle parent.
     *
     * @return Model
     */
    public function getParentModel()
    {
        return $this->parentModel;
    }

    /**
     * Retourne l'instance du modèle associé.
     *
     * @return Model
     */
    public function getRelatedModel()
    {
        return $this->relatedModel;
    }

    /**
     * Retourne le contenu de la relation.
     *
     * @return string
     */
    abstract public function getContent();

    /**
     * Retourne le contenu du stub de la relation.
     *
     * @return string
     */
    protected function getRelationStubContent()
    {
        $filename = lcfirst(class_basename(static::class));

        return $this->getStubContent("relations/$filename");
    }
}
