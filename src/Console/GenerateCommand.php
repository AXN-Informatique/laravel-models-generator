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
        // Construit les instances des modèles
        $models = $this->builder->getModels();

        $onlyTables = $this->option('table');
        $update = $this->option('update');
        $preview = $this->option('preview');
        
        if ($preview) {
            $this->error('Preview mode: files are not touched');
        }

        // Génère et/ou met à jour les modèles
        if (!empty($onlyTables)) {
            foreach ($onlyTables as $table) {
                $this->generateOrUpdateModel($models[$table], $update, $preview);
            }
        } else {
            $config = $this->laravel['config'];
            $ignoredTables = $config->get('models-generator.ignored_tables', []);

            foreach ($models as $model) {
                if (in_array($model->getTable(), $ignoredTables)) {
                    continue;
                }

                $this->generateOrUpdateModel($model, $update, $preview);
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
            if (!$update || !$model->needsUpdate()) {
                return;
            }

            if (!$preview) {
                $model->updateFile();
            }

            $this->line('<comment>Updated:</comment> '.$model->getName().' in '.$model->getPath());
        }
        // Sinon : création du fichier
        else {
            if (!$preview) {
                $model->generateFile();
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
            ['preview', 'p', InputOption::VALUE_NONE, 'Displays results without touching files.'],
		];
	}
}
