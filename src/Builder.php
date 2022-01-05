<?php

namespace Axn\ModelsGenerator;

use Doctrine\DBAL\Schema\AbstractSchemaManager as DoctrineSchemaManager;
use Doctrine\DBAL\Types\DateTimeType;
use Illuminate\Config\Repository as Config;
use Illuminate\Support\Str;

class Builder
{
    /**
     * Instance de configuration de Laravel.
     *
     * @var Config
     */
    protected $config;

    /**
     * SchemaManager de Doctrine/DBAL pour la récupération d'informations sur les tables.
     *
     * @var DoctrineSchemaManager
     */
    protected $doctrineSchemaManager;

    /**
     * Liste des instances des modèles par nom de table.
     *
     * @var array[Model]
     */
    protected $models = [];

    /**
     * Constructeur.
     *
     * @param  Config $config
     * @param  DoctrineSchemaManager $doctrineSchemaManager
     * @return void
     */
    public function __construct(Config $config, DoctrineSchemaManager $doctrineSchemaManager)
    {
        $this->config = $config;
        $this->doctrineSchemaManager = $doctrineSchemaManager;
    }

    /**
     * Initialise puis retourne les instances des modèles.
     *
     * @return array[Model]
     */
    public function getModels()
    {
        foreach ($this->doctrineSchemaManager->listTables() as $table) {
            // Crée l'instance du modèle pour la table correspondante
            $this->models[$table->getName()] = $this->createModel($table->getName());
        }

        // Ajoute les relations 1-n et 1-1 selon les contraintes définies dans la BDD
        foreach ($this->models as $model) {
            $this->addRelationsAccordingToConstraints($model);
        }

        return $this->models;
    }

    /**
     * Crée une nouvelle instance Model pour une table donnée.
     *
     * @param  string $table
     * @return Model
     */
    protected function createModel($table)
    {
        list($groupDir, $tableWithoutGroup) = $this->getGroupingInfo($table);
        $groupDir = ($groupDir ? '/'.$groupDir : '');
        $groupNs = str_replace('/', '\\', $groupDir);

        $modelName = $this->buildModelName($tableWithoutGroup);
        $modelNs = $this->getConfig('models_ns').$groupNs;
        $modelPath = str_replace(
            ['/', '\\'],
            DIRECTORY_SEPARATOR,
            $this->getConfig('models_dir')."$groupDir/$modelName.php"
        );

        $relations = $this->createModelRelations($modelName, $groupDir, $groupNs);

        $model = new Model($table, $tableWithoutGroup, $modelName, $modelNs, $modelPath, $relations);

        if (!$this->hasTimestampsColumns($table)) {
            $model->setTimestamped(false);
        }

        if (in_array($table, $this->getConfig('ignored_tables', []))) {
            $model->setIgnored(true);
        }

        return $model;
    }

    /**
     * Crée une nouvelle instance Relations pour un modèle donné.
     *
     * @param  string $modelName
     * @param  string $groupDir
     * @param  string $groupNs
     * @return Relations
     */
    protected function createModelRelations($modelName, $groupDir, $groupNs)
    {
        $relationsName = $modelName.'Relations';
        $relationsNs = $this->getConfig('relations_ns').$groupNs;
        $relationsPath = str_replace(
            ['/', '\\'],
            DIRECTORY_SEPARATOR,
            $this->getConfig('relations_dir')."$groupDir/$relationsName.php"
        );

        return new Relations($relationsName, $relationsNs, $relationsPath);
    }

    /**
     * Retourne les informations sur le groupement, à savoir :
     *   - le sous-répertoire du groupe
     *   - le nom de la table sans la partie servant à grouper
     *
     * @param  string $table
     * @return array
     */
    protected function getGroupingInfo($table)
    {
        if ($groupDir = $this->getConfig("groupings.$table")) {
            return [$groupDir, $table];
        }

        foreach ($this->getConfig('groupings', []) as $groupKey => $groupDir) {
            if (strpos($groupKey, '^') === 0
                && preg_match('/'.$groupKey.'_(.+)/', $table, $matches)) {

                return [$groupDir, $matches[1]];
            }
        }

        return [null, $table];
    }

    /**
     * Détermine et retourne le nom du modèle à partir du nom de la table.
     *
     * @param  string $tableWithoutGroup
     * @return string
     */
    protected function buildModelName($tableWithoutGroup)
    {
        $singularRules = $this->getConfig('singular_rules', []);
        $modalName = '';

        foreach (explode('_', $tableWithoutGroup) as $index => $word) {
            $singularWord = null;

            foreach ($singularRules as $rule => $singular) {
                if (preg_match('/'.$rule.'$/', $word)) {
                    $singularWord = preg_replace('/'.$rule.'$/', $singular, $word);
                    break;
                }
            }

            $modalName .= ucfirst($singularWord ?: Str::singular($word));
        }

        return $modalName;
    }

    /**
     * Ajoute les relations HasOne, HasMany et BelongsTo à un modèle selon les
     * contraintes de clés étrangères définies dans la BDD.
     *
     * @param  Model $model
     * @return void
     */
    protected function addRelationsAccordingToConstraints(Model $model)
    {
        $constraints = $this->doctrineSchemaManager->listTableForeignKeys($model->getTable());

        foreach ($constraints as $constraint) {
            $relatedModel = $this->models[$constraint->getForeignTableName()];
            $foreignKey = $constraint->getLocalColumns()[0];

            if (!$this->isIgnoredRelation($relatedModel, $model, $foreignKey)) {
                if ($this->isOneToOneRelation($relatedModel, $model, $foreignKey)) {
                    $relatedModel->hasOne($model, $foreignKey);
                } else {
                    $relatedModel->hasMany($model, $foreignKey);
                }
            }

            if (!$this->isIgnoredRelation($model, $relatedModel, $foreignKey, true)) {
                $model->belongsTo($relatedModel, $foreignKey);
            }
        }
    }

    /**
     * Est-ce que la relation entre les deux modèles est à ignorer ?
     *
     * @param  Model  $fromModel
     * @param  Model  $toModel
     * @param  string $foreignKey
     * @param  bool   $isBelongsTo
     * @return bool
     */
    protected function isIgnoredRelation(Model $fromModel, Model $toModel, $foreignKey, $isBelongsTo = false)
    {
        $ignoredRelations = $this->getConfig('ignored_relations', []);

        $fromTable = $fromModel->getTable();
        $toTable = $toModel->getTable();

        if ($isBelongsTo) {
            if (in_array("$fromTable.$foreignKey:$toTable", $ignoredRelations)
                || in_array("*.$foreignKey:$toTable", $ignoredRelations)) {

                return true;
            }
        } else {
            if (in_array("$fromTable:$toTable.$foreignKey", $ignoredRelations)
                || in_array("$fromTable:*.$foreignKey", $ignoredRelations)) {

                return true;
            }
        }

        return false;
    }

    /**
     * Est-ce que les deux modèles sont en relation 1-1 ?
     *
     * @param  Model  $fromModel
     * @param  Model  $toModel
     * @param  string $foreignKey
     * @return bool
     */
    protected function isOneToOneRelation(Model $fromModel, Model $toModel, $foreignKey)
    {
        $oneToOneRelations = $this->getConfig('one_to_one_relations', []);

        $from = $fromModel->getTable();
        $to = $toModel->getTable().'.'.$foreignKey;

        if (in_array("$from:$to", $oneToOneRelations)) {
            return true;
        }

        return false;
    }

    /**
     * Est-ce que la table possède les champs "created_at" et "updated_at" ?
     *
     * @param  string $table
     * @return bool
     */
    protected function hasTimestampsColumns($table)
    {
        $columns = $this->doctrineSchemaManager->listTableColumns($table);

        if (isset($columns['created_at']) && $columns['created_at']->getType() instanceof DateTimeType
            && isset($columns['updated_at']) && $columns['updated_at']->getType() instanceof DateTimeType) {

            return true;
        }

        return false;
    }

    /**
     * Retourne la valeur d'une option de configuration.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    protected function getConfig($key, $default = null)
    {
        return $this->config->get("models-generator.$key", $default);
    }
}
