<?php

namespace Axn\ModelsGenerator;

use ReflectionClass;
use Illuminate\Config\Repository as Config;
use Axn\ModelsGenerator\Drivers\Driver;

class Generator
{
    /**
     * Répertoire des templates des modèles.
     *
     * @var string
     */
    protected static $templatesDir;

    /**
     * Liste des templates des classes et relations.
     *
     * Voir méthode "getTemplate".
     *
     * @var array[string]
     */
    protected static $templates = [];

    /**
     * Liste des instances des générateurs de chaque modèle.
     *
     * @var array[static]
     */
    protected static $instances = [];

    /**
     * Liste des noms de tables par nom de modèle.
     *
     * @var array
     */
    protected static $tablesByModel = [];

    /**
     * Driver de connexion à la BDD pour la récupération d'informations
     * sur les tables.
     *
     * @var \Axn\ModelsGenerator\Drivers\Driver
     */
    protected $driver;

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
     * Nom de classe du repository.
     *
     * @var string
     */
    protected $repositoryName;

    /**
     * Namespace du repository.
     *
     * @var string
     */
    protected $repositoryNamespace;

    /**
     * Chemin complet vers le fichier du repository.
     *
     * @var string
     */
    protected $repositoryPath;

    /**
     * Nom du contrat.
     *
     * @var string
     */
    protected $contractName;

    /**
     * Namespace du contrat.
     *
     * @var string
     */
    protected $contractNamespace;

    /**
     * Chemin complet vers le fichier du contrat.
     *
     * @var string
     */
    protected $contractPath;

    /**
     * Nom de classe de la façade.
     *
     * @var string
     */
    protected $facadeName;

    /**
     * Namespace de la façade.
     *
     * @var string
     */
    protected $facadeNamespace;

    /**
     * Chemin complet vers le fichier de la façade.
     *
     * @var string
     */
    protected $facadePath;

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
    protected $morphToRelation = null;

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
     * Initialise les générateurs de chaque table.
     *
     * @param  \Illuminate\Config\Repository      $config
     * @param  \Axn\ModelsGenerator\Drivers\Driver $driver
     * @return array[static]
     */
    public static function initGenerators(Config $config, Driver $driver)
    {
        $tablesNames = $driver->getTablesNames();
        $generators = [];

        self::$templatesDir = $config->get('models-generator.templates_dir');

        foreach ($tablesNames as $tableName) {
            $generator = new static($config, $driver, $tableName);
            $generators[] = $generator;

            static::setInstance($tableName, $generator);
            static::$tablesByModel[$generator->getModelName()] = $tableName;
        }

        foreach ($generators as $generator) {
            $generator->createRelationsByConstraints();
            $generator->createPolymorphicRelations();
        }
        foreach ($config->get('models-generator.pivot_tables', []) as $pivotTable) {
            static::getInstance($pivotTable)->defineAsPivot();
        }
        foreach ($generators as $generator) {
            $generator->sortRelations();
        }

        return $generators;
    }

    /**
     * Constructeur.
     *
     * @param  \Illuminate\Config\Repository       $config
     * @param  \Axn\ModelsGenerator\Drivers\Driver $driver
     * @param  string $tableName
     * @return void
     */
    public function __construct(Config $config, Driver $driver, $tableName)
    {
        $this->driver = $driver;
        $this->tableName = $tableName;

        $groups = $config->get('models-generator.groups');

        if ($pivotTablesGroup = $config->get('models-generator.pivot_tables_group', '')) {
            $groups[$pivotTablesGroup] = $config->get('models-generator.pivot_tables');
        }

        $groupDir = $this->searchGroup($tableName, $groups);
        $groupNs = str_replace('/', '\\', $groupDir);

        $modelDir = $this->mkdir($config->get('models-generator.models.dir').$groupDir);
        $repositoryDir = $this->mkdir($config->get('models-generator.repositories.dir').$groupDir);
        $contractDir = $this->mkdir($config->get('models-generator.contracts.dir').$groupDir);
        $facadeDir = $this->mkdir($config->get('models-generator.facades.dir').$groupDir);

        if ($config->has("models-generator.forced_names.$tableName")) {
            $this->modelName = $config->get("models-generator.forced_names.$tableName");
        } else {
            $modelWordsFormatter = function($value) {
                return ucfirst(str_singular($value));
            };
            $this->modelName = implode('', array_map($modelWordsFormatter, explode('_', $tableName)));
        }
        $this->modelNamespace = $config->get('models-generator.models.ns').$groupNs;
        $this->modelPath = $modelDir.'/'.$this->modelName.'.php';

        $this->repositoryName = 'Eloquent'.$this->modelName.'Repository';
        $this->repositoryNamespace = $config->get('models-generator.repositories.ns').$groupNs;
        $this->repositoryPath = $repositoryDir.'/'.$this->repositoryName.'.php';

        $this->contractName = $this->modelName.'Repository';
        $this->contractNamespace = $config->get('models-generator.contracts.ns').$groupNs;
        $this->contractPath = $contractDir.'/'.$this->contractName.'.php';

        $this->facadeName = $this->modelName.'Facade';
        $this->facadeNamespace = $config->get('models-generator.facades.ns').$groupNs;
        $this->facadePath = $facadeDir.'/'.$this->facadeName.'.php';
    }

    /**
     * Recherche le groupe auquel a été affectée la table.
     *
     * @param  string $tableName
     * @param  array  $groups
     * @return string
     */
    protected function searchGroup($tableName, array $groups = [])
    {
        foreach ($groups as $groupName => $groupTables) {
            if (in_array($tableName, $groupTables)) {
                return '/'.$groupName;
            }
        }

        return '';
    }

    /**
     * Crée les sous-dossiers d'un dossier si ceux-ci n'existent pas puis retourne
     * le chemin du dossier.
     *
     * @param  string $dirPath
     * @return string
     */
    protected function mkdir($dirPath)
    {
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0755, true);
        }

        return $dirPath;
    }

    /**
     * Retourne le driver de connexion à la BDD.
     *
     * @return \Axn\ModelsGenerator\Drivers\Driver
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
     * Retourne le nom du repository.
     *
     * @return string
     */
    public function getRepositoryName()
    {
        return $this->repositoryName;
    }

    /**
     * Retourne le namespace du repository.
     *
     * @return string
     */
    public function getRepositoryNamespace()
    {
        return $this->repositoryNamespace;
    }

    /**
     * Retourne le chemin du repository.
     *
     * @return string
     */
    public function getRepositoryPath()
    {
        return $this->repositoryPath;
    }

    /**
     * Retourne le nom du contrat.
     *
     * @return string
     */
    public function getContractName()
    {
        return $this->contractName;
    }

    /**
     * Retourne le namespace du contrat.
     *
     * @return string
     */
    public function getContractNamespace()
    {
        return $this->contractNamespace;
    }

    /**
     * Retourne le chemin du contrat.
     *
     * @return string
     */
    public function getContractPath()
    {
        return $this->contractPath;
    }

    /**
     * Retourne le nom de la façade.
     *
     * @return string
     */
    public function getFacadeName()
    {
        return $this->facadeName;
    }

    /**
     * Retourne le namespace de la façade.
     *
     * @return string
     */
    public function getFacadeNamespace()
    {
        return $this->facadeNamespace;
    }

    /**
     * Retourne le chemin de la façade.
     *
     * @return string
     */
    public function getFacadePath()
    {
        return $this->facadePath;
    }

    /**
     * Génère le fichier du modèle.
     *
     * @return string
     */
    public function generateModel()
    {
        $path = $this->getModelPath();

        // Si modèle déjà existant : mise à jour des relations grâces aux tags
        if (is_file($path)) {
            $content = preg_replace(
                '/#RELATIONS.*#END_RELATIONS/Uus', $this->getRelationsContent(),
                file_get_contents($path)
            );
            $updated = true;
        } else {
            $content = $this->getModelContent();
            $updated = false;
        }

        if (@file_put_contents($path, $content)) {
            return "Model ".$this->getModelName()." ".($updated ? "updated" : "generated");
        } else {
            return "Error while writing model ".$this->getModelName();
        }
    }

    /**
     * Génère le fichier du repository.
     *
     * @return string
     */
    public function generateRepository()
    {
        if (@file_put_contents($this->getRepositoryPath(), $this->getRepositoryContent())) {
            return "Repository ".$this->getRepositoryName()." generated";
        } else {
            return "Error while writing repository ".$this->getRepositoryName();
        }
    }

    /**
     * Génère le fichier du contrat.
     *
     * @return string
     */
    public function generateContract()
    {
        // On génère au préalable une interface vierge pour éviter les erreurs
        // lors de l'utilisation de la réflection sur le repository
        @file_put_contents($this->getContractPath(), $this->getContractContent(false));

        if (@file_put_contents($this->getContractPath(), $this->getContractContent())) {
            return "Contract ".$this->getContractName()." generated";
        } else {
            return "Error while writing contract ".$this->getContractName();
        }
    }

    /**
     * Génère le fichier de la façade.
     *
     * @return string
     */
    public function generateFacade()
    {
        if (@file_put_contents($this->getFacadePath(), $this->getFacadeContent())) {
            return "Facade ".$this->getFacadeName()." generated";
        } else {
            return "Error while writing facade ".$this->getFacadeName();
        }
    }

    /**
     * Crée les relations entre modèles en analysant les contraintes définis
     * sur leurs tables respectives.
     *
     * @return void
     */
    protected function createRelationsByConstraints()
    {
        $tableName = $this->getTableName();
        $constraintsInfo = $this->getDriver()->getTableConstraintsInfo($tableName);

        foreach ($constraintsInfo as $constraint) {
            $relatedTable = $constraint['relatedTable'];
            $foreignKey = $constraint['foreignKey'];

            static::getInstance($relatedTable)->addHasManyRelation($tableName, $foreignKey);

            $this->addBelongsToRelation($relatedTable, $foreignKey);
        }
    }

    /**
     * Crée les relations polymorphiques entre modèles en analysant les champs
     * "morph type" (qui sont de type "enum" avec les noms des tables pouvant
     * être liées).
     *
     * @return void
     */
    protected function createPolymorphicRelations()
    {
        $tableName = $this->getTableName();
        $morphTypes = $this->getDriver()->getTableMorphTypes($tableName);

        if (!empty($morphTypes)) {
            foreach ($morphTypes['values'] as $relatedModel) {
                $relatedTable = static::$tablesByModel[$relatedModel];

                static::getInstance($relatedTable)->addMorphManyRelation($tableName, $morphTypes['name']);
            }

            $this->addMorphToRelation($morphTypes['name']);
        }
    }

    /**
     * Réorganise les relations par ordre alphabétique pour éviter que l'odre
     * ne change entre deux générations, ce qui créerait un diff avec les outils
     * de versionning.
     *
     * @return void
     */
    protected function sortRelations()
    {
        $sorter = function($a, $b) {
            return strnatcmp($a[0], $b[0]);
        };
        usort($this->hasManyRelations, $sorter);
        usort($this->belongsToRelations, $sorter);
        usort($this->belongsToManyRelations, $sorter);
        usort($this->morphManyRelations, $sorter);
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
        $relatedModel = static::getInstance($relatedTable)->getModelName();
        $methodName = lcfirst(str_plural($relatedModel));

        if ($relatedModel == $this->getModelName()) {
            $methodName = 'children';
        }
        elseif (preg_match('/_id_([a-z0-9_]+)$/U', $foreignKey, $m)) {
            $methodName .= 'Via'.studly_case($m[1]);
        }

        $this->hasManyRelations[] = [$relatedTable, $foreignKey, $methodName];
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
        $relatedModel = static::getInstance($relatedTable)->getModelName();
        $methodName = lcfirst($relatedModel);

        if ($relatedModel == $this->getModelName()) {
            $methodName = 'parent';
        }
        elseif (preg_match('/_id_([a-z0-9_]+)$/U', $foreignKey, $m)) {
            $methodName .= 'Via'.studly_case($m[1]);
        }

        $this->belongsToRelations[] = [$relatedTable, $foreignKey, $methodName];
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
        $relatedModel = static::getInstance($relatedTable)->getModelName();
        $methodName = lcfirst(str_plural($relatedModel));

        $this->belongsToManyRelations[] = [$relatedTable, $pivotTable, $foreignKey, $otherKey, $methodName];
    }

    /**
     * Ajoute une relation MorphMany vers un autre modèle.
     *
     * @param  array $relatedTable
     * @return void
     */
    protected function addMorphManyRelation($relatedTable, $morphName)
    {
        $relatedModel = static::getInstance($relatedTable)->getModelName();
        $methodName = lcfirst(str_plural($relatedModel));

        $this->morphManyRelations[] = [$relatedTable, $morphName, $methodName];
    }

    /**
     * Indique que le modèle à une relation polymorphique.
     *
     * @param  string $name
     * @return void
     */
    protected function addMorphToRelation($name)
    {
        $this->morphToRelation = $name;
    }

    /**
     * Définit la table associée à ce générateur comme étant une table pivot, c'est-à-dire :
     * prend les deux premières relations belongsTo et leur ajoute à chacune une relation
     * belongsToMany l'une vers l'autre.
     *
     * @return void
     */
    protected function defineAsPivot()
    {
        $belongsToRelations = array_slice($this->belongsToRelations, 0, 2);

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
     * Retourne le contenu généré pour le modèle.
     *
     * @return string
     */
    protected function getModelContent()
    {
        $template = $this->getModelTemplate();

        $content = strtr($template, [
            '{{namespace}}' => $this->getModelNamespace(),
            '{{name}}'      => $this->getModelName(),
            '{{tableName}}' => $this->getTableName(),
            '{{relations}}' => $this->getRelationsContent(),
        ]);

        return $content;
    }

    /**
     * Retourne le contenu généré pour le repository.
     *
     * @return string
     */
    protected function getRepositoryContent()
    {
        $template = $this->getRepositoryTemplate();

        $content = strtr($template, [
            '{{namespace}}'         => $this->getRepositoryNamespace(),
            '{{name}}'              => $this->getRepositoryName(),
            '{{contractNamespace}}' => $this->getContractNamespace(),
            '{{contractName}}'      => $this->getContractName(),
            '{{modelNamespace}}'    => $this->getModelNamespace(),
            '{{modelName}}'         => $this->getModelName(),
        ]);

        return $content;
    }

    /**
     * Retourne le contenu généré pour le contrat.
     *
     * @param  boolean $withMethods
     * @return string
     */
    protected function getContractContent($withMethods = true)
    {
        $template = $this->getContractTemplate();

        $content = strtr($template, [
            '{{namespace}}' => $this->getContractNamespace(),
            '{{name}}'      => $this->getContractName(),
            '{{methods}}'   => $withMethods ? $this->getContractMethods() : '',
        ]);

        return $content;
    }

    /**
     * Retourne le contenu généré pour la façade.
     *
     * @return string
     */
    protected function getFacadeContent()
    {
        $template = $this->getFacadeTemplate();

        $content = strtr($template, [
            '{{namespace}}'         => $this->getFacadeNamespace(),
            '{{name}}'              => $this->getFacadeName(),
            '{{contractNamespace}}' => $this->getContractNamespace(),
            '{{contractName}}'      => $this->getContractName(),
        ]);

        return $content;
    }

    /**
     * Retourne le contenu des relations du modèle.
     *
     * @return string
     */
    protected function getRelationsContent()
    {
        return "#RELATIONS"
            .$this->getBelongsToManyRelationsContent()
            .$this->getHasManyRelationsContent()
            .$this->getBelongsToRelationsContent()
            .$this->getMorphManyRelationsContent()
            .$this->getMorphToRelationContent()
            ."#END_RELATIONS";
    }

    /**
     * Retourne le code des méthodes définissant les relations HasMany.
     *
     * @return string
     */
    protected function getHasManyRelationsContent()
    {
        if (empty($this->hasManyRelations)) {
            return '';
        }

        $content = '';
        $template = $this->getRelationTemplate('has-many');

        foreach ($this->hasManyRelations as $relation) {
            list($relatedTable, $foreignKey, $methodName) = $relation;

            $related = static::getInstance($relatedTable);
            $relatedNamespace = $related->getModelNamespace();
            $relatedModel = $related->getModelName();

            $content .= strtr($template, [
                '{{relatedTable}}' => $relatedTable,
                '{{relatedModel}}' => $relatedNamespace.'\\'.$relatedModel,
                '{{foreignKey}}'   => $foreignKey,
                '{{methodName}}'   => $methodName,
            ]);
        }

        return $content;
    }

    /**
     * Retourne le code des méthodes définissant les relations BelongsTo.
     *
     * @return string
     */
    protected function getBelongsToRelationsContent()
    {
        if (empty($this->belongsToRelations)) {
            return '';
        }

        $content = '';
        $template = $this->getRelationTemplate('belongs-to');

        foreach ($this->belongsToRelations as $relation) {
            list($relatedTable, $foreignKey, $methodName) = $relation;

            $related = static::getInstance($relatedTable);
            $relatedNamespace = $related->getModelNamespace();
            $relatedModel = $related->getModelName();

            $content .= strtr($template, [
                '{{relatedTable}}' => $relatedTable,
                '{{relatedModel}}' => $relatedNamespace.'\\'.$relatedModel,
                '{{foreignKey}}'   => $foreignKey,
                '{{methodName}}'   => $methodName,
            ]);
        }

        return $content;
    }

    /**
     * Retourne le code des méthodes définissant les relations BelongsToMany.
     *
     * @return string
     */
    protected function getBelongsToManyRelationsContent()
    {
        if (empty($this->belongsToManyRelations)) {
            return '';
        }

        $content = '';
        $template = $this->getRelationTemplate('belongs-to-many');

        foreach ($this->belongsToManyRelations as $relation) {
            list($relatedTable, $pivotTable, $foreignKey, $otherKey, $methodName) = $relation;

            $related = static::getInstance($relatedTable);
            $relatedNamespace = $related->getModelNamespace();
            $relatedModel = $related->getModelName();

            $content .= strtr($template, [
                '{{relatedTable}}' => $relatedTable,
                '{{relatedModel}}' => $relatedNamespace.'\\'.$relatedModel,
                '{{pivotTable}}'   => $pivotTable,
                '{{foreignKey}}'   => $foreignKey,
                '{{otherKey}}'     => $otherKey,
                '{{methodName}}'   => $methodName,
            ]);
        }

        return $content;
    }

    /**
     * Retourne le code des méthodes définissant les relations MorphMany.
     *
     * @return string
     */
    protected function getMorphManyRelationsContent()
    {
        if (empty($this->morphManyRelations)) {
            return '';
        }

        $content = '';
        $template = $this->getRelationTemplate('morph-many');

        foreach ($this->morphManyRelations as $relation) {
            list($relatedTable, $morphName, $methodName) = $relation;

            $related = static::getInstance($relatedTable);
            $relatedNamespace = $related->getModelNamespace();
            $relatedModel = $related->getModelName();

            $content .= strtr($template, [
                '{{relatedTable}}' => $relatedTable,
                '{{relatedModel}}' => $relatedNamespace.'\\'.$relatedModel,
                '{{morphName}}'    => $morphName,
                '{{methodName}}'   => $methodName,
            ]);
        }

        return $content;
    }

    /**
     * Retourne le code de la méthode définissant la relation MorphTo.
     *
     * @return string
     */
    protected function getMorphToRelationContent()
    {
        if (empty($this->morphToRelation)) {
            return '';
        }

        $template = $this->getRelationTemplate('morph-to');

        return strtr($template, [
            '{{methodName}}' => $this->morphToRelation,
        ]);
    }

    /**
     * Retourne, à l'aide de la réflection de classe, les méthodes du repository
     * à ajouter au contrat.
     *
     * @return string
     */
    protected function getContractMethods()
    {
        $rClass = new ReflectionClass($this->getRepositoryNamespace().'\\'.$this->getRepositoryName());
        $rMethods = $rClass->getMethods();
        $methods = '';

        foreach ($rMethods as $rMethod) {
            if ($rMethod->getDeclaringClass()->getName() === $rClass->getName()
                && $rMethod->isPublic()
                && strpos($rMethod->getName(), '__') !== 0)
            {
                $rParameters = $rMethod->getParameters();
                $params = [];

                foreach ($rParameters as $rParameter) {
                    if ($rParameter->isArray()) {
                        $type = 'array ';
                    }
                    elseif ($rParameterClass = $rParameter->getClass()) {
                        $type = '\\'.$rParameterClass->getName().' ';
                    }
                    else {
                        $type = '';
                    }

                    $params[] = $type.'$'.$rParameter->getName();
                }

                $methods .= ($methods !== '' ? "\n\n" : '')
                    .'    '.$rMethod->getDocComment()."\n"
                    .'    public function '.$rMethod->getName().'('.implode(', ', $params).');';
            }
        }

        return $methods;
    }

    /**
     * Retourne le contenu d'un template pour la construction du modèle.
     *
     * @return string
     */
    protected function getModelTemplate()
    {
        if (is_file($file = self::$templatesDir.'/model.txt')) {
            return $this->getTemplate($file);
        } else {
            return $this->getTemplate(__DIR__.'/templates/model.txt');
        }
    }

    /**
     * Retourne le contenu d'un template pour la construction du repository.
     *
     * @return string
     */
    protected function getRepositoryTemplate()
    {
        if (is_file($file = self::$templatesDir.'/repository.txt')) {
            return $this->getTemplate($file);
        } else {
            return $this->getTemplate(__DIR__.'/templates/repository.txt');
        }
    }

    /**
     * Retourne le contenu d'un template pour la construction du contrat.
     *
     * @return string
     */
    protected function getContractTemplate()
    {
        return $this->getTemplate(__DIR__.'/templates/contract.txt');
    }

    /**
     * Retourne le contenu d'un template pour la construction de la façade.
     *
     * @return string
     */
    protected function getFacadeTemplate()
    {
        return $this->getTemplate(__DIR__.'/templates/facade.txt');
    }

    /**
     * Retourne le contenu d'un template pour la construction d'une relation.
     *
     * @param  string $relationName
     * @return string
     */
    protected function getRelationTemplate($relationName)
    {
        return $this->getTemplate(__DIR__.'/templates/relations/'.$relationName.'.txt');
    }

    /**
     * Retourne le contenu d'un template.
     *
     * @param  string $path
     * @return string
     */
    protected function getTemplate($path)
    {
        if (!isset(self::$templates[$path])) {
            self::$templates[$path] = file_get_contents($path);
        }

        return self::$templates[$path];
    }
}
