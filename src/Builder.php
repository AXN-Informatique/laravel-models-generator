<?php

namespace Axn\ModelsGenerator;

use Illuminate\Config\Repository as Config;
use Axn\ModelsGenerator\Drivers\Driver;

class Builder
{
    /**
     * Instance de configuration de Laravel.
     *
     * @var Config
     */
    protected $config;

    /**
     * Driver de connexion à la BDD pour la récupération d'informations sur les tables.
     *
     * @var Driver
     */
    protected $driver;

    /**
     * Liste des instances des modèles par nom de table.
     *
     * @var array[Model]
     */
    protected $models = [];

    /**
     * Liste des instances des pivots par nom de table.
     *
     * @var array[Pivot]
     */
    protected $pivots = [];

    /**
     * Constructeur.
     *
     * @param  Config $config
     * @param  Driver $driver
     * @return void
     */
    public function __construct(Config $config, Driver $driver)
    {
        $this->config = $config;
        $this->driver = $driver;
    }

    /**
     * Initialise puis retourne les instances des modèles.
     *
     * @return array[Model]
     */
    public function getModels()
    {
        $tables = $this->driver->getTablesNames();
        $pivotTables = $this->getConfig('pivot_tables', []);

        foreach ($tables as $table) {
            // Crée l'instance du modèle pour la table correspondante
            $this->models[$table] = $this->createModel($table);

            // Crée l'instance du pivot si la table est définie ou reconnue comme tel
            if (array_key_exists($table, $pivotTables)) {
                $this->pivots[$table] = new Pivot($table, $pivotTables[$table]);
            }
            elseif (in_array($table, $pivotTables) || strpos($table, '_has_') !== false) {
                $this->pivots[$table] = new Pivot($table);
            }
        }

        // Ajoute les relations 1-n et 1-1 selon les contraintes définies dans la BDD
        foreach ($this->models as $model) {
            $this->addRelationsAccordingToConstraints($model);
        }

        // Ajoute les relations n-n via les instances des pivots
        foreach ($this->pivots as $pivot) {
            $pivot->addBelongsToManyRelationsToRelatedModels();
        }

        // Ajoute les relations polymorphiques selon les informations renseignées dans la config
        foreach ($this->getConfig('polymorphic_relations', []) as $relation => $relatedTables) {
            list($table, $morphName) = explode('.', $relation);

            $this->addPolymorphicRelations($this->models[$table], $morphName, $relatedTables);
        }

        return $this->models;
    }

    /**
     * Crée une nouvelle instance de modèle pour une table donnée.
     *
     * @param  string $table
     * @return Model
     */
    protected function createModel($table)
    {
        $group = $this->getConfig("groupings.$table");
        $groupDir = ($group ? '/'.$group : '');
        $groupNs = str_replace('/', '\\', $groupDir);

        $modelName = $this->buildModelName($table);
        $modelNs = $this->getConfig('models_ns').$groupNs;
        $modelPath = str_replace(
            ['/', '\\'],
            DIRECTORY_SEPARATOR,
            $this->getConfig('models_dir')."$groupDir/$modelName.php"
        );

        $relations = $this->createModelRelations($modelName, $groupDir, $groupNs);
        $ignored = in_array($table, $this->getConfig('ignored_tables', []));

        return new Model($table, $modelName, $modelNs, $modelPath, $relations, $ignored);
    }

    /**
     * Crée une nouvelle instance de modèle pour une table donnée.
     *
     * @param  string $modelName
     * @param  string $groupDir
     * @param  string $groupNs
     * @return Relations
     */
    protected function createModelRelations($modelName, $groupDir, $groupNs)
    {
        $relationsName = $modelName;
        $relationsNs = $this->getConfig('relations_ns').$groupNs;
        $relationsPath = str_replace(
            ['/', '\\'],
            DIRECTORY_SEPARATOR,
            $this->getConfig('relations_dir')."$groupDir/$relationsName.php"
        );

        return new Relations($relationsName, $relationsNs, $relationsPath);
    }

    /**
     * Détermine et retourne le nom du modèle à partir du nom de la table.
     *
     * @param  string $table
     * @return string
     */
    protected function buildModelName($table)
    {
        $singularRules = ['^has' => 'has'] + $this->getConfig('singular_rules');
        $modalName = '';

        foreach (explode('_', $table) as $word) {
            $singularWord = null;

            foreach ($singularRules as $rule => $singular) {
                if (preg_match('/'.$rule.'$/', $word)) {
                    $singularWord = preg_replace('/'.$rule.'$/', $singular, $word);
                    break;
                }
            }

            $modalName .= ucfirst($singularWord ?: str_singular($word));
        }

        return $modalName;
    }

    /**
     * Ajoute les relations HasOne, HasMany et BelongsTo à un modèle selon les
     * contraintes de clés étrangères définies dans la BDD. Si pivot, le modèle
     * associé en BelongsTo est ajouté à l'instance Pivot correspondante pour
     * l'ajout des relations BelongsToMany dans un second temps.
     *
     * @param  Model $model
     * @return void
     */
    protected function addRelationsAccordingToConstraints(Model $model)
    {
        $constraintsInfo = $this->driver->getTableConstraintsInfo($model->getTable());

        foreach ($constraintsInfo as $constraint) {
            $relatedModel = $this->models[$constraint['relatedTable']];
            $foreignKey = $constraint['foreignKey'];

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

            if (isset($this->pivots[$model->getTable()])) {
                $this->pivots[$model->getTable()]->setRelatedModel($foreignKey, $relatedModel);
            }
        }
    }

    /**
     * Ajoute les relations MorphOne, MorphMany et MorphTo à un modèle.
     *
     * @param  string $morphName
     * @param  array  $relatedTables
     * @return void
     */
    protected function addPolymorphicRelations(Model $model, $morphName, array $relatedTables)
    {
        foreach ($relatedTables as $relatedTable) {
            $relatedModel = $this->models[$relatedTable];

            if ($this->isOneToOneRelation($relatedModel, $model, $morphName)) {
                $relatedModel->morphOne($model, $morphName);
            } else {
                $relatedModel->morphMany($model, $morphName);
            }
        }

        $model->morphTo($morphName);
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

        $from = $fromModel->getTable().( $isBelongsTo ? '.'.$foreignKey : '');
        $to = $toModel->getTable().( !$isBelongsTo ? '.'.$foreignKey : '');

        if (in_array("$from:$to", $ignoredRelations)) {
            return true;
        }

        return false;
    }

    /**
     * Est-ce que les deux modèles sont en relation 1-1 ?
     *
     * @param  Model  $fromModel
     * @param  Model  $toModel
     * @param  string $fkOrMorphName
     * @return bool
     */
    protected function isOneToOneRelation(Model $fromModel, Model $toModel, $fkOrMorphName)
    {
        $oneToOneRelations = $this->getConfig('one_to_one_relations', []);

        $from = $fromModel->getTable();
        $to = $toModel->getTable().'.'.$fkOrMorphName;

        if (in_array("$from:$to", $oneToOneRelations)) {
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
