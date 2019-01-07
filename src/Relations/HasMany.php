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
        return camel_case($this->relatedModel->getTableWithoutPrefix())
             . $this->getNamePrecision();
    }

    /**
     * Retourne la précision à concaténer au nom de la relation lorsque le nom
     * de la clé étrangère n'est pas standard.
     *
     * @return string
     */
    protected function getNamePrecision()
    {
        $snakeParentModelName = snake_case($this->parentModel->getName());

        $recognizedForeignKeyNames = [
            $snakeParentModelName.'_id',
            'id_'.$snakeParentModelName,
        ];

        foreach ($recognizedForeignKeyNames as $recognizedName) {
            if ($this->foreignKey === $recognizedName) {
                return '';
            }
        }

        return 'Via'.studly_case($this->foreignKey);
    }
}
