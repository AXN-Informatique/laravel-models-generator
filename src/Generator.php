<?php

namespace Axn\ModelsGenerator;

use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command;
use Axn\ModelsGenerator\Drivers\Driver;

class Generator
{
    /**
     * Liste des templates des classes et relations.
     *
     * Voir méthode "getStub".
     *
     * @var array[string]
     */
    protected static $stubs = [];

    /**
     * Liste des instances des générateurs de chaque modèle.
     *
     * @var array[static]
     */
    protected static $instances = [];

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
     * Instance de la commande pour afficher des messages en console.
     *
     * @var Command|null
     */
    protected $command;

    /**
     * Nom de la table concernée.
     *
     * @var string
     */
    protected $tableName;

    /**
     * Nom de classe du modèle.
     *
     * @var string
     */
    protected $modelName;

    /**
     * Namespace du modèle.
     *
     * @var string
     */
    protected $modelNamespace;

    /**
     * Chemin complet vers le fichier du modèle.
     *
     * @var string
     */
    protected $modelPath;

    /**
     * Liste des relations HasOne.
     *
     * @var array
     */
    protected $hasOneRelations = [];

    /**
     * Liste des relations HasMany.
     *
     * @var array
     */
    protected $hasManyRelations = [];

    /**
     * Liste des relations BelongsTo.
     *
     * @var array
     */
    protected $belongsToRelations = [];

    /**
     * Liste des relations BelongsToMany.
     *
     * @var array
     */
    protected $belongsToManyRelations = [];

    /**
     * Liste des relations MorphOne.
     *
     * @var array
     */
    protected $morphOneRelations = [];

    /**
     * Liste des relations MorphMany.
     *
     * @var array
     */
    protected $morphManyRelations = [];

    /**
     * Liste des relations MorphTo.
     *
     * @var string|null
     */
    protected $morphToRelations = [];

    /**
     * Retourne l'instance d'un générateur.
     *
     * @param  string $tableName
     * @return static
     */
    public static function getInstance($tableName)
    {
        return static::$instances[$tableName];
    }

    /**
     * Ajoute/modifie l'instance d'un générateur.
     *
     * @param  string $tableName
     * @param  static $instance
     * @return void
     */
    public static function setInstance($tableName, $instance)
    {
        static::$instances[$tableName] = $instance;
    }

    /**
     * Initialise puis retourne les générateurs de chaque table.
     *
     * @param  Config       $config
     * @param  Driver       $driver
     * @param  Command|null $command
     * @return array[static]
     */
    public static function initAndGetInstances(Config $config, Driver $driver, $command = null)
    {
        $tablesNames = $driver->getTablesNames();
        $generators = [];

        // Crée les instances Generator pour chaque table
        foreach ($tablesNames as $tableName) {
            $generator = new static($config, $driver, $command, $tableName);
            $generators[] = $generator;

            static::setInstance($tableName, $generator);
        }

        // Ajoute les relations 1-n (et 1-1) via les contraintes définies dans la BDD
        foreach ($generators as $generator) {
            $generator->parseDbConstraints();
        }

        // Ajoute les relations n-n via les pivots renseignés dans la config
        foreach ($config->get('models-generator.pivot_tables', []) as $pivotTable) {
            if (is_array($pivotTable)) {
                static::getInstance($pivotTable[0])->pivot($pivotTable[1], $pivotTable[2]);
            } else {
                static::getInstance($pivotTable)->pivot();
            }
        }

        // Ajoute les relations polymorphiques via les informations renseignées dans la config
        foreach ($config->get('models-generator.polymorphic_relations', []) as $relation => $relatedTables) {
            list($tableName, $morphName) = explode('.', $relation);

            static::getInstance($tableName)->polymorphic($morphName, $relatedTables);
        }

        // Tri les relations (ordre d'apparition dans la classe modèle)
        foreach ($generators as $generator) {
            $generator->sortRelations();
        }

        return $generators;
    }

    /**
     * Constructeur.
     *
     * @param  Config       $config
     * @param  Driver       $driver
     * @param  Command|null $command
     * @param  string       $tableName
     * @return void
     */
    public function __construct(Config $config, Driver $driver, $command, $tableName)
    {
        $this->config = $config;
        $this->driver = $driver;
        $this->command = $command;
        $this->tableName = $tableName;

        $group = $this->config->get("models-generator.groups.$tableName");
        $groupDir = ($group ? '/'.$group : '');
        $groupNs = str_replace('/', '\\', $groupDir);

        $this->modelName = $this->tableToModel($tableName);
        $this->modelNamespace = $config->get('models-generator.models_ns').$groupNs;
        $this->modelPath = $config->get('models-generator.models_dir').$groupDir.'/'.$this->modelName.'.php';
    }

    /**
     * Retourne le driver de connexion à la BDD.
     *
     * @return Driver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Retourne le nom de la table.
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Retourne le nom du modèle.
     *
     * @return string
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    /**
     * Retourne le namespace du modèle.
     *
     * @return string
     */
    public function getModelNamespace()
    {
        return $this->modelNamespace;
    }

    /**
     * Retourne le chemin du modèle.
     *
     * @return string
     */
    public function getModelPath()
    {
        return $this->modelPath;
    }

    /**
     * Génère le fichier du modèle.
     *
     * @param  boolean $update
     * @return void
     */
    public function generateModel($update = false)
    {
        $ignoredTables = $this->config->get('models-generator.ignored_tables', []);
        $path = $this->getModelPath();

        if (in_array($this->getTableName(), $ignoredTables) || is_file($path) && !$update) {
            return;
        }

        // Si modèle déjà existant : mise à jour des relations grâces aux tags
        if (is_file($path)) {
            $content = preg_replace(
                '/#GENERATED_RELATIONS.*#END_GENERATED_RELATIONS/Uus',
                $this->getRelationsContent(),
                file_get_contents($path)
            );
            $updated = true;
        } else {
            $this->createMissingDirs($path);
            $content = $this->getModelContent();
            $updated = false;
        }

        file_put_contents($path, $content);

        $this->message(
            '<info>Model '.$this->getModelName().' '
            . ($updated ? 'updated' : 'generated').':</info> '
            . realpath($this->getModelPath())
        );
    }

    /**
     * Analyse les contraintes de clés étrangères dans la BDD pour ajouter les
     * relations HasOne, HasMany et BelongsTo.
     *
     * @return void
     */
    protected function parseDbConstraints()
    {
        $constraintsInfo = $this->getDriver()->getTableConstraintsInfo($this->getTableName());

        foreach ($constraintsInfo as $constraint) {
            $relatedTable = $constraint['relatedTable'];
            $foreignKey = $constraint['foreignKey'];
            $related = static::getInstance($relatedTable);

            if ($related->isOneToOneRelation($this->getTableName(), $foreignKey)) {
                $related->addHasOneRelation($this->getTableName(), $foreignKey);
            } else {
                $related->addHasManyRelation($this->getTableName(), $foreignKey);
            }

            $this->addBelongsToRelation($relatedTable, $foreignKey);
        }
    }

    /**
     * Définit la table comme étant un pivot pour ajouter les relations BelongsToMany.
     *
     * @param  string|null $fk1
     * @param  string|null $fk2
     * @return void
     */
    protected function pivot($fk1 = null, $fk2 = null)
    {
        if (!$fk1 || !$fk2) {
            // Si les clés à utiliser ne sont pas spécifiées, on utilise les deux
            // premières relations BelongsTo qui ont été trouvées
            $belongsToRelations = array_slice($this->belongsToRelations, 0, 2);
        } else {
            // Sinon, on recherche les relations BelongsTo qui sont concernées
            // par les clés $fk1 et $fk2
            $belongsToRelations = [];

            foreach ($this->belongsToRelations as $btr) {
                if ($btr[1] === $fk1 || $btr[1] === $fk2) {
                    $belongsToRelations[] = $btr;
                }
            }
        }

        static::getInstance($belongsToRelations[0][0])->addBelongsToManyRelation(
            $belongsToRelations[1][0], // related table
            $this->getTableName(),     // pivot table
            $belongsToRelations[0][1], // foreign key
            $belongsToRelations[1][1]  // other key
        );

        static::getInstance($belongsToRelations[1][0])->addBelongsToManyRelation(
            $belongsToRelations[0][0], // related table
            $this->getTableName(),     // pivot table
            $belongsToRelations[1][1], // foreign key
            $belongsToRelations[0][1]  // other key
        );
    }

    /**
     * Définit la table comme étant polymorphique pour ajouter les relations
     * MorphOne, MorphMany et MorphTo.
     *
     * @param  string $morphName
     * @param  array  $relatedTables
     * @return void
     */
    protected function polymorphic($morphName, array $relatedTables)
    {
        foreach ($relatedTables as $relatedTable) {
            $related = static::getInstance($relatedTable);

            if ($related->isOneToOneRelation($this->getTableName(), $morphName)) {
                $related->addMorphOneRelation($this->getTableName(), $morphName);
            } else {
                $related->addMorphManyRelation($this->getTableName(), $morphName);
            }
        }

        $this->addMorphToRelation($morphName);
    }

    /**
     * Réorganise les relations par ordre alphabétique pour éviter que l'ordre
     * ne change entre deux générations, ce qui créerait un diff avec les outils
     * de versionning.
     *
     * @return void
     */
    protected function sortRelations()
    {
        ksort($this->hasOneRelations);
        ksort($this->hasManyRelations);
        ksort($this->belongsToRelations);
        ksort($this->belongsToManyRelations);
        ksort($this->morphOneRelations);
        ksort($this->morphManyRelations);
    }

    /**
     * Ajoute une relation HasOne vers un autre modèle.
     *
     * @param  string $relatedTable
     * @param  string $foreignKey
     * @return void
     */
    protected function addHasOneRelation($relatedTable, $foreignKey)
    {
        if ($this->isIgnoredRelation($relatedTable, $foreignKey)) {
            return;
        }

        $methodName = lcfirst(static::getInstance($relatedTable)->getModelName());
        $precision = studly_case(str_replace('_id', '', $foreignKey));

        if ($this->getModelName() !== $precision) {
            $methodName .= 'Via'.$precision;
        }

        $this->hasOneRelations[$methodName] = [$relatedTable, $foreignKey, '', ''];
    }

    /**
     * Ajoute une relation HasMany vers un autre modèle.
     *
     * @param  string $relatedTable
     * @param  string $foreignKey
     * @return void
     */
    protected function addHasManyRelation($relatedTable, $foreignKey)
    {
        if ($this->isIgnoredRelation($relatedTable, $foreignKey)) {
            return;
        }

        if (in_array($relatedTable, $this->config->get('models-generator.pivot_tables', []))) {
            $methodName = 'pivot'.studly_case($relatedTable);
        } else {
            $methodName = camel_case($relatedTable);
            $precision = studly_case(str_replace('_id', '', $foreignKey));

            if ($this->getModelName() !== $precision) {
                $methodName .= 'Via'.$precision;
            }
        }

        $this->hasManyRelations[$methodName] = [$relatedTable, $foreignKey, '', ''];
    }

    /**
     * Ajoute une relation BelongsTo vers un autre modèle.
     *
     * @param  string $relatedTable
     * @param  string $foreignKey
     * @return void
     */
    protected function addBelongsToRelation($relatedTable, $foreignKey)
    {
        if ($this->isIgnoredRelation($relatedTable, $foreignKey, true)) {
            return;
        }

        $methodName = camel_case(str_replace('_id', '', $foreignKey));

        $this->belongsToRelations[$methodName] = [$relatedTable, $foreignKey, '', ''];
    }

    /**
     * Ajoute une relation BelongsToMany vers un autre modèle.
     *
     * @param  string $relatedTable
     * @param  string $pivotTable
     * @param  string $foreignKey
     * @param  string $otherKey
     * @return void
     */
    protected function addBelongsToManyRelation($relatedTable, $pivotTable, $foreignKey, $otherKey)
    {
        $methodName = camel_case($relatedTable);

        $this->belongsToManyRelations[$methodName] = [$relatedTable, $foreignKey, $pivotTable, $otherKey];
    }

    /**
     * Ajoute une relation MorphOne vers un autre modèle.
     *
     * @param  array $relatedTable
     * @return void
     */
    protected function addMorphOneRelation($relatedTable, $morphName)
    {
        $methodName = lcfirst(static::getInstance($relatedTable)->getModelName());

        $this->morphOneRelations[$methodName] = [$relatedTable, $morphName, '', ''];
    }

    /**
     * Ajoute une relation MorphMany vers un autre modèle.
     *
     * @param  array $relatedTable
     * @return void
     */
    protected function addMorphManyRelation($relatedTable, $morphName)
    {
        $methodName = camel_case($relatedTable);

        $this->morphManyRelations[$methodName] = [$relatedTable, $morphName, '', ''];
    }

    /**
     * Ajoute une relation MorphTo.
     *
     * @param  string $morphName
     * @return void
     */
    protected function addMorphToRelation($morphName)
    {
        $this->morphToRelations[$morphName] = $morphName;
    }

    /**
     * Est-ce que la table est en relation 1-1 avec une autre table donnée ?
     *
     * @param  string $relatedTable
     * @param  string $fkOrMorphName
     * @return boolean
     */
    protected function isOneToOneRelation($relatedTable, $fkOrMorphName)
    {
        $oneToOneRelations = $this->config->get('models-generator.one_to_one_relations', []);

        $from = $this->getTableName();
        $to = "$relatedTable.$fkOrMorphName";

        if (in_array("$from:$to", $oneToOneRelations)) {
            return true;
        }

        return false;
    }

    /**
     * Est-ce la relation est à ignorer pour cette table ?
     *
     * @param  string  $relatedTable
     * @param  string  $foreignKey
     * @param  boolean $isBelongsTo
     * @return boolean
     */
    protected function isIgnoredRelation($relatedTable, $foreignKey, $isBelongsTo = false)
    {
        $ignoredRelations = $this->config->get('models-generator.ignored_relations', []);

        $from = $this->getTableName().($isBelongsTo ? ".$foreignKey" : '');
        $to = $relatedTable.(!$isBelongsTo ? ".$foreignKey" : '');

        if (in_array("$from:$to", $ignoredRelations)) {
            return true;
        }

        return false;
    }

    /**
     * Vérifie qu'un nom de méthode pour une relation n'apparait qu'une seule fois.
     *
     * @param  string $methodName
     * @return boolean
     */
    protected function checkRelationIsUnique($methodName)
    {
        if (array_key_exists($methodName, $this->hasOneRelations)
            || array_key_exists($methodName, $this->hasManyRelations)
            || array_key_exists($methodName, $this->belongsToRelations)
            || array_key_exists($methodName, $this->belongsToManyRelations)
            || array_key_exists($methodName, $this->morphOneRelations)
            || array_key_exists($methodName, $this->morphManyRelations)
            || array_key_exists($methodName, $this->morphToRelations)) {

            $this->message(
                '<error>Model '.$this->getModelName().': '
                . 'relation name "'.$methodName.'" is duplicated!</error>'
            );

            return false;
        }

        return true;
    }

    /**
     * Retourne le contenu généré pour le modèle.
     *
     * @return string
     */
    protected function getModelContent()
    {
        $stub = $this->getModelStub();

        return strtr($stub, [
            '{{namespace}}' => $this->getModelNamespace(),
            '{{name}}'      => $this->getModelName(),
            '{{tableName}}' => $this->getTableName(),
            '{{relations}}' => $this->getRelationsContent(),
        ]);
    }

    /**
     * Retourne le contenu généré pour les relations du modèle.
     *
     * @return string
     */
    protected function getRelationsContent()
    {
        return '#GENERATED_RELATIONS'
            . $this->getRelationsContentByType('belongsToMany')
            . $this->getRelationsContentByType('hasOne')
            . $this->getRelationsContentByType('hasMany')
            . $this->getRelationsContentByType('belongsTo')
            . $this->getRelationsContentByType('morphOne')
            . $this->getRelationsContentByType('morphMany')
            . $this->getMorphToRelationsContent()
            . '#END_GENERATED_RELATIONS';
    }

    /**
     * Retourne le code des méthodes des relations du type spécifié (BelongsTo,
     * HasOne, HasMany, MorphOne, MorphMany ou BelongsToMany).
     *
     * @param  string $type
     * @return string
     */
    protected function getRelationsContentByType($type)
    {
        $relationProperty = $type.'Relations';

        if (empty($this->{$relationProperty})) {
            return '';
        }

        $content = '';
        $stub = $this->getRelationStub($type);

        foreach ($this->{$relationProperty} as $methodName => $relation) {
            if (!$this->checkRelationIsUnique($methodName)) {
                continue;
            }

            list($relatedTable, $fkOrMorphName, $pivotTable, $otherKey) = $relation;

            $related = static::getInstance($relatedTable);
            $relatedNamespace = $related->getModelNamespace();
            $relatedModel = $related->getModelName();

            $content .= strtr($stub, [
                '{{relatedTable}}' => $relatedTable,
                '{{relatedModel}}' => $relatedNamespace.'\\'.$relatedModel,
                '{{foreignKey}}'   => $fkOrMorphName,
                '{{morphName}}'    => $fkOrMorphName,
                '{{methodName}}'   => $methodName,
                '{{pivotTable}}'   => $pivotTable,
                '{{otherKey}}'     => $otherKey
            ]);
        }

        return $content;
    }

    /**
     * Retourne le code de la méthode de la relation MorphTo.
     *
     * @return string
     */
    protected function getMorphToRelationsContent()
    {
        if (empty($this->morphToRelations)) {
            return '';
        }

        $content = '';
        $stub = $this->getRelationStub('morphTo');

        foreach ($this->morphToRelations as $morphName) {
            if (!$this->checkRelationIsUnique($morphName)) {
                continue;
            }

            $content .= strtr($stub, [
                '{{methodName}}' => $morphName,
            ]);
        }

        return $content;
    }

    /**
     * Retourne le contenu du template du modèle.
     *
     * @return string
     */
    protected function getModelStub()
    {
        return $this->getStub('model');
    }

    /**
     * Retourne le contenu du template d'un type de relation.
     *
     * @param  string $type
     * @return string
     */
    protected function getRelationStub($type)
    {
        return $this->getStub("relations/$type");
    }

    /**
     * Retourne le contenu d'un template.
     *
     * @param  string $name
     * @return string
     */
    protected function getStub($name)
    {
        if (!isset(self::$stubs[$name])) {
            if (!is_file($path = base_path("resources/stubs/vendor/models-generator/$name.stub"))) {
                $path = __DIR__."/../resources/stubs/$name.stub";
            }

            self::$stubs[$name] = file_get_contents($path);
        }

        return self::$stubs[$name];
    }

    /**
     * Donne le nom du modèle à partir du nom de la table.
     *
     * @param  string $tableName
     * @return string
     */
    protected function tableToModel($tableName)
    {
        $forcedName = $this->config->get("models-generator.forced_names.$tableName");

        if ($forcedName) {
            return $forcedName;
        }

        $wordsFormatter = function($value) {
            return ucfirst(str_singular($value));
        };

        return implode('', array_map($wordsFormatter, explode('_', $tableName)));
    }

    /**
     * Crée les sous-dossiers d'un fichier si ceux-ci n'existent pas.
     *
     * @param  string $filePath
     * @return void
     */
    protected function createMissingDirs($filePath)
    {
        if (!is_dir($dirPath = dirname($filePath))) {
            mkdir($dirPath, 0755, true);
        }
    }

    /**
     * Permet d'afficher un message en console.
     *
     * @param string $message
     */
    protected function message($message)
    {
        if ($this->command instanceof Command) {
            $this->command->line($message);
        }
    }
}
