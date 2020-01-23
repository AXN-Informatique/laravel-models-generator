<?php

namespace Axn\ModelsGenerator\Relations;

use Illuminate\Support\Str;

class HasMany extends BelongsTo
{
    /**
     * Construit et retourne le nom de la relation.
     *
     * @return string
     */
    protected function buildName()
    {
        return Str::camel($this->relatedModel->getTableWithoutGroup())
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
        $snakeParentModelName = Str::snake($this->parentModel->getName());

        $recognizedForeignKeyNames = [
            $snakeParentModelName.'_id',
            'id_'.$snakeParentModelName,
        ];

        foreach ($recognizedForeignKeyNames as $recognizedName) {
            if ($this->foreignKey === $recognizedName) {
                return '';
            }
        }

        return 'Via'.Str::studly($this->foreignKey);
    }
}
