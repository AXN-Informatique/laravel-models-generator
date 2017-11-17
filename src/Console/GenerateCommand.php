<?php

namespace Axn\ModelsGenerator\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Axn\ModelsGenerator\Model;
use Axn\ModelsGenerator\Builder;

class GenerateCommand extends Command
{
    /**
     * Nom de la commande.
     *
     * @var string
     */
    protected $name = 'models:generate';

    /**
     * Description de la commande.
     *
     * @var string
     */
    protected $description = 'Generates Eloquent models files from DB';

    /**
     * Instance du builder des modèles.
     *
     * @var Builder
     */
    protected $builder;

    /**
     * Constructeur.
     *
     * @param  Builder $builder
     * @return void
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;

        parent::__construct();
    }

    /**
     * Exécute la commande.
     *
     * @return void
     */
    public function handle()
    {
        $onlyTables = $this->option('table');
        $update = $this->option('update');
        $preview = $this->option('preview');

        $config = $this->laravel['config'];
        $ignoredTables = $config->get('models-generator.ignored_tables', []);
        
        if ($preview) {
            $this->error('Preview mode: files are not generated/modified');
        }

        // Construit les instances modèles
        $models = $this->builder->getModels();

        // Génère et/ou met à jour les modèles
        if (!empty($onlyTables)) {
            foreach ($onlyTables as $table) {
                $this->generateOrUpdateModel($models[$table], $update, $preview);
            }
        } else {
            foreach ($models as $model) {
                if (!in_array($model->getTable(), $ignoredTables)) {
                    $this->generateOrUpdateModel($model, $update, $preview);
                }
            }
        }
    }

    /**
     * Génère ou met à jour un modèle.
     *
     * @param  Model $model
     * @param  bool  $update
     * @param  bool  $preview
     * @return void
     */
    protected function generateOrUpdateModel(Model $model, $update, $preview)
    {
        // Fichier déjà existant : mise à jour des relations
        if (is_file($model->getPath())) {
            if (!$update) {
                return;
            }

            $fileContent = file_get_contents($model->getPath());
    
            $hasTags = preg_match(
                '/#GENERATED_RELATIONS.*#END_GENERATED_RELATIONS/Uus',
                $fileContent,
                $matches
            );
    
            if (!$hasTags) {
                return;
            }
    
            $oldRelations = str_replace("\r\n", "\n", $matches[0]);
            $newRelations = str_replace("\r\n", "\n", $model->getRelationsContent());
    
            if ($oldRelations === $newRelations) {
                return;
            }

            if (!$preview) {
                file_put_contents(
                    $model->getPath(),
                    str_replace($oldRelations, $newRelations, $fileContent)
                );
            }

            $this->line('<comment>Updated:</comment> '.$model->getName().' in '.$model->getPath());
        }
        // Sinon : création du fichier
        else {
            if (!$preview) {
                $dirPath = dirname($model->getPath());
        
                if (!is_dir($dirPath)) {
                    mkdir($dirPath, 0755, true);
                }
        
                file_put_contents($model->getPath(), $model->getContent());
            }

            $this->line('<info>Created:</info> '.$model->getName().' in '.$model->getPath());
        }
    }

    /**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			//['example', InputArgument::REQUIRED, 'An example argument.'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['table', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Generate models only for these tables.'],
            ['update', 'u', InputOption::VALUE_NONE, 'Update relations in existing models.'],
            ['preview', 'p', InputOption::VALUE_NONE, 'Display operations without generate or update.'],
		];
	}
}
