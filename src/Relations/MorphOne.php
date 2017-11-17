<?php

namespace Axn\ModelsGenerator\Relations;

class MorphOne extends MorphMany
{
    /**
     * Construit et retourne le nom de la relation.
     *
     * @return string
     */
    protected function buildName()
    {
        return lcfirst($this->relatedModel->getName());
    }
}
