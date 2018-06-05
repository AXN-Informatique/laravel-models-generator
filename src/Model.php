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
     * Indique si le modèle est ignoré.
     *
     * @var bool
     */
    protected $ignored;

    /**
     * Instance des relations.
     *
     * @var Relations
     */
    protected $relations;

    /**
     * Contenu du fichier déjà existant du modèle.
     *
     * @var string
     */
    protected $fileContent;

    /**
     * Constructeur.
     *
     * @param  string    $table
     * @param  string    $name
     * @param  string    $namespace
     * @param  string    $path
     * @param  Relations $relations
     * @param  bool      $ignored
     * @return void
     */
    public function __construct($table, $name, $namespace, $path, Relations $relations, $ignored = false)
    {
        $this->table = $table;
        $this->name = $name;
        $this->namespace = $namespace;
        $this->path = $path;
        $this->relations = $relations;
        $this->ignored = $ignored;
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
     * Retourne l'instance des relations du modèle'.
     *
     * @return string
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * Indique si le modèle est ignoré.
     *
     * @return bool
     */
    public function isIgnored()
    {
        return $this->ignored;
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
        if ($relatedModel->isIgnored()) {
            return;
        }

        $this->relations->add(
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
        if ($relatedModel->isIgnored()) {
            return;
        }

        $this->relations->add(
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
        if ($relatedModel->isIgnored()) {
            return;
        }

        $this->relations->add(
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
        if ($relatedModel->isIgnored()) {
            return;
        }

        $this->relations->add(
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
        if ($relatedModel->isIgnored()) {
            return;
        }

        $this->relations->add(
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
        if ($relatedModel->isIgnored()) {
            return;
        }

        $this->relations->add(
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
        $this->relations->add(
            new Relations\MorphTo($this, $morphName)
        );
    }

    /**
     * Retourne le contenu généré.
     *
     * @return string
     */
    public function getContent()
    {
        return strtr($this->getStubContent('model'), [
            '{{namespace}}' => $this->namespace,
            '{{name}}'      => $this->name,
            '{{table}}'     => $this->table,
            '{{relations}}' => $this->relations->getTrait(),
        ]);
    }

    /**
     * Écrit le contenu généré dans le fichier du modèle.
     *
     * @return void
     */
    public function writeContent()
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
}
