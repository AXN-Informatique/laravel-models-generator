<?php

namespace Axn\ModelsGenerator;

use Axn\ModelsGenerator\Traits\HasStub;

class Model
{
    use HasStub;

    /**
     * Nom de la table concernée.
     *
     * @var string
     */
    protected $table;

    /**
     * Nom de classe du modèle.
     *
     * @var string
     */
    protected $name;

    /**
     * Namespace du modèle.
     *
     * @var string
     */
    protected $namespace;

    /**
     * Chemin complet vers le fichier du modèle.
     *
     * @var string
     */
    protected $path;

    /**
     * Liste des relations.
     *
     * @var array[Relation]
     */
    protected $relations = [];

    /**
     * Contenu du fichier déjà existant du modèle.
     *
     * @var string
     */
    protected $fileContent;

    /**
     * Constructeur.
     *
     * @param  string $table
     * @param  string $name
     * @param  string $namespace
     * @param  string $path
     * @return void
     */
    public function __construct($table, $name, $namespace, $path)
    {
        $this->table = $table;
        $this->name = $name;
        $this->namespace = $namespace;
        $this->path = $path;
    }

    /**
     * Retourne le nom de la table.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Retourne le nom du modèle.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Retourne le namespace du modèle.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Retourne la classe du modèle.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->namespace.'\\'.$this->name;
    }

    /**
     * Retourne le chemin du modèle.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Ajoute une relation BelongsTo vers un autre modèle.
     *
     * @param  Model  $relatedModel
     * @param  string $foreignKey
     * @return void
     */
    public function belongsTo(Model $relatedModel, $foreignKey)
    {
        $this->addRelation(
            new Relations\BelongsTo($this, $relatedModel, $foreignKey)
        );
    }

    /**
     * Ajoute une relation BelongsToMany vers un autre modèle.
     *
     * @param  Model  $relatedModel
     * @param  string $pivotTable
     * @param  string $foreignKey
     * @param  string $otherKey
     * @return void
     */
    public function belongsToMany(Model $relatedModel, $pivotTable, $foreignKey, $otherKey)
    {
        $this->addRelation(
            new Relations\BelongsToMany($this, $relatedModel, $pivotTable, $foreignKey, $otherKey)
        );
    }

    /**
     * Ajoute une relation HasMany vers un autre modèle.
     *
     * @param  Model  $relatedModel
     * @param  string $foreignKey
     * @return void
     */
    public function hasMany(Model $relatedModel, $foreignKey)
    {
        $this->addRelation(
            new Relations\HasMany($this, $relatedModel, $foreignKey)
        );
    }

    /**
     * Ajoute une relation HasOne vers un autre modèle.
     *
     * @param  Model  $relatedModel
     * @param  string $foreignKey
     * @return void
     */
    public function hasOne(Model $relatedModel, $foreignKey)
    {
        $this->addRelation(
            new Relations\HasOne($this, $relatedModel, $foreignKey)
        );
    }

    /**
     * Ajoute une relation MorphMany vers un autre modèle.
     *
     * @param  Model  $relatedModel
     * @param  string $morphName
     * @return void
     */
    public function morphMany(Model $relatedModel, $morphName)
    {
        $this->addRelation(
            new Relations\MorphMany($this, $relatedModel, $morphName)
        );
    }

    /**
     * Ajoute une relation MorphOne vers un autre modèle.
     *
     * @param  Model  $relatedModel
     * @param  string $morphName
     * @return void
     */
    public function morphOne(Model $relatedModel, $morphName)
    {
        $this->addRelation(
            new Relations\MorphOne($this, $relatedModel, $morphName)
        );
    }

    /**
     * Ajoute une relation MorphTo.
     *
     * @param  string $morphName
     * @return void
     */
    public function morphTo($morphName)
    {
        $this->addRelation(
            new Relations\MorphTo($this, $morphName)
        );
    }

    /**
     * Ajoute une relation si celle-ci n'existe pas déjà.
     *
     * @param  Relations\Relation $relation
     * @return void
     */
    protected function addRelation(Relations\Relation $relation)
    {
        if (isset($this->relations[$relation->getName()])) {
            throw new \Exception(
                'Relation '.$relation->getName().' is duplicated in model '.$this->getName()
            );
        }

        $this->relations[$relation->getName()] = $relation;
    }

    /**
     * Retourne le contenu du modèle.
     *
     * @return string
     */
    public function getContent()
    {
        return strtr($this->getStubContent('model'), [
            '{{namespace}}' => $this->namespace,
            '{{name}}'      => $this->name,
            '{{table}}'     => $this->table,
            '{{relations}}' => $this->getRelationsContent(),
        ]);
    }

    /**
     * Retourne le contenu des relations du modèle.
     *
     * @return string
     */
    public function getRelationsContent()
    {
        ksort($this->relations);

        $content = '#GENERATED_RELATIONS';

        foreach ($this->relations as $name => $relation) {
            $content .= $relation->getContent();
        }

        $content .= '#END_GENERATED_RELATIONS';

        return $content;
    }

    /**
     * Retourne le contenu du fichier modèle existant.
     *
     * @return string
     */
    public function getFileContent()
    {
        if (!isset($this->fileContent)) {
            $this->fileContent = file_get_contents($this->getPath());
        }

        return $this->fileContent;
    }

    /**
     * Retourne le contenu des relations du fichier modèle existant.
     *
     * @return string
     */
    public function getFileRelationsContent()
    {
        $hasTags = preg_match(
            '/#GENERATED_RELATIONS.*#END_GENERATED_RELATIONS/Uus',
            $this->getFileContent(),
            $matches
        );

        if (!$hasTags) {
            return '';
        }

        return $matches[0];
    }

    /**
     * Génère le modèle.
     *
     * @return void
     */
    public function generateFile()
    {
        $dirPath = dirname($this->getPath());

        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0755, true);
        }

        file_put_contents(
            $this->getPath(),
            $this->getContent()
        );
    }

    /**
     * Met à jour les relations dans le fichier modèle existant.
     *
     * @return void
     */
    public function updateFile()
    {
        file_put_contents(
            $this->getPath(),
            str_replace(
                $this->getFileRelationsContent(),
                $this->getRelationsContent(),
                $this->getFileContent()
            )
        );
    }

    /**
     * Indique si le fichier du modèle nécessite d'être mis à jour.
     *
     * @return bool
     */
    public function needsUpdate()
    {
        $new = $this->getRelationsContent();
        $old = $this->getFileRelationsContent();

        $normalize = function($content) {
            return str_replace("\r\n", "\n", $content);
        };

        return $normalize($new) !== $normalize($old);
    }
}
