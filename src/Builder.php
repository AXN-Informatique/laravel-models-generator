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
        if (!empty($this->models)) {
            return $this->models;
        }

        $tables = $this->driver->getTablesNames();

        foreach ($tables as $table) {
            // Crée l'instance du modèle pour la table correspondante
            $this->models[$table] = $this->createModel($table);

            // Crée l'instance du pivot si la table est reconnue comme tel
            if (strpos($table, '_has_') !== false) {
                $this->pivots[$table] = new Pivot($table);
            }
        }

        // Crée les instances des pivots pour les tables indiquées dans la config
        foreach ($this->config->get('models-generator.pivot_tables', []) as $table) {
            $this->pivots[$table] = new Pivot($table);
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
        foreach ($this->config->get('models-generator.polymorphic_relations', []) as $relation => $relatedTables) {
            list($table, $morphName) = explode('.', $relation);

            $this->addPolymorphicRelations($this->models[$table], $morphName, $relatedTables);
        }

        return $this->models;
    }

    /**
     * Crée une nouvelle instance de modèle pour une table donnée.
     *
     * @param  string $table
     * @return void
     */
    protected function createModel($table)
    {
        $group = $this->config->get("models-generator.groupings.$table");
        $groupDir = ($group ? '/'.$group : '');
        $groupNs = str_replace('/', '\\', $groupDir);

        $modelName = $this->buildModelName($table);
        $modelNs = $this->config->get('models-generator.models_ns').$groupNs;
        $modelPath = $this->config->get('models-generator.models_dir').$groupDir.'/'.$modelName.'.php';

        return new Model($table, $modelName, $modelNs, $modelPath);
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
        $ignoredRelations = $this->config->get('models-generator.ignored_relations', []);

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
        $oneToOneRelations = $this->config->get('models-generator.one_to_one_relations', []);

        $from = $fromModel->getTable();
        $to = $toModel->getTable().'.'.$fkOrMorphName;

        if (in_array("$from:$to", $oneToOneRelations)) {
            return true;
        }

        return false;
    }

    /**
     * Détermine et retourne le nom du modèle à partir du nom de la table.
     *
     * @param  string $table
     * @return string
     */
    protected function buildModelName($table)
    {
        $forcedName = $this->config->get("models-generator.forced_names.$table");

        if ($forcedName) {
            return $forcedName;
        }

        $wordsFormatter = function ($value) {
            if ($value === 'has') {
                return 'Has';
            }

            return ucfirst(str_singular($value));
        };

        return implode('', array_map($wordsFormatter, explode('_', $table)));
    }
}