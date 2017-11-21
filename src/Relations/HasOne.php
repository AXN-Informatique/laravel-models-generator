<?php

namespace Axn\ModelsGenerator\Relations;

class HasOne extends HasMany
{
    /**
     * Construit et retourne le nom de la relation.
     *
     * @return string
     */
    protected function buildName()
    {
        return lcfirst($this->relatedModel->getName())
            . $this->getNamePrecision();
    }
}
