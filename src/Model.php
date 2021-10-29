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
     * Nom de la table concernée, sans la partie servant à grouper.
     *
     * @var string
     */
    protected $tableWithoutGroup;

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
     * Instance des relations.
     *
     * @var Relations
     */
    protected $relations;

    /**
     * Indique si la table associée au modèle possède les champs `created_at`
     * et `updated_at`.
     *
     * @var bool
     */
    protected $timestamped = true;

    /**
     * Indique si la génération du modèle doit être ignorée.
     *
     * @var bool
     */
    protected $ignored = false;

    /**
     * Constructeur.
     *
     * @param  string    $table
     * @param  string    $tableWithoutGroup
     * @param  string    $name
     * @param  string    $namespace
     * @param  string    $path
     * @param  Relations $relations
     * @return void
     */
    public function __construct(
        $table, $tableWithoutGroup, $name, $namespace, $path, Relations $relations)
    {
        $this->table = $table;
        $this->tableWithoutGroup = $tableWithoutGroup;
        $this->name = $name;
        $this->namespace = $namespace;
        $this->path = $path;
        $this->relations = $relations;
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
     * Retourne le nom de la table, sans la partie servant à grouper.
     *
     * @return string
     */
    public function getTableWithoutGroup()
    {
        return $this->tableWithoutGroup;
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
     * Indique si la génération du modèle doit être ignorée.
     *
     * @return bool
     */
    public function isIgnored()
    {
        return $this->ignored;
    }

    /**
     * Modifie la valeur de l'attribut "timestamped".
     *
     * @param  bool $timestamped
     * @return void
     */
    public function setTimestamped($timestamped)
    {
        $this->timestamped = (bool) $timestamped;
    }

    /**
     * Modifie la valeur de l'attribut "ignored".
     *
     * @param  bool $ignored
     * @return void
     */
    public function setIgnored($ignored)
    {
        $this->ignored = (bool) $ignored;
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
     * Retourne le contenu généré.
     *
     * @return string
     */
    public function getContent()
    {
        return strtr($this->getStubContent('model'), [
            '{{namespace}}'  => $this->namespace,
            '{{name}}'       => $this->name,
            '{{table}}'      => $this->table,
            '{{relations}}'  => $this->relations->getTrait(),
            '{{traitName}}'  => $this->relations->getName(),
            '{{timestamps}}' => $this->timestamped ? 'true' : 'false',
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
