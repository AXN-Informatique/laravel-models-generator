<?php

namespace Axn\ModelsGenerator\Relations;

class HasMany extends BelongsTo
{
    /**
     * Construit et retourne le nom de la relation.
     *
     * @return string
     */
    protected function buildName()
    {
        return camel_case($this->relatedModel->getTable())
            . $this->buildNamePrecision();
    }

    /**
     * Construit la précision à concaténer au nom de la relation lorsque le nom
     * de la clé étrangère est différent du nom de la table parente.
     *
     * @return string
     */
    protected function buildNamePrecision()
    {
        $precision = studly_case(str_replace('_id', '', $this->foreignKey));

        if ($this->parentModel->getName() === $precision) {
            return '';
        }

        return 'Via'.$precision;
    }
}
