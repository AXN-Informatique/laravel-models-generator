<?php

namespace Axn\ModelsGenerator\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Axn\ModelsGenerator\Model;
use Axn\ModelsGenerator\Relations;
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
        $tables = $this->option('table');
        $preview = $this->option('preview');

        // Construit les instances des modèles
        $models = $this->builder->getModels();

        if ($preview) {
            $this->error('Preview mode: files are not touched');
        }

        // Lance la création/modification des fichiers
        foreach ($models as $model) {
            if ($model->isIgnored()
                || $tables && !in_array($model->getTable(), $tables)) {

                continue;
            }

            $this->generateModel($model, $preview);
            $this->generateModelRelations($model->getRelations(), $preview);
        }
    }

    /**
     * Crée ou met à jour le fichier d'un modèle.
     *
     * @param  Model $model
     * @param  bool  $preview
     * @return void
     */
    protected function generateModel(Model $model, $preview)
    {
        // Fichier déjà existant : on ne touche à rien
        if (is_file($model->getPath())) {
            return;
        }

        if (!$preview) {
            $model->writeContent();
        }

        $this->line('<info>Created Model:</info> '.$model->getName().' in '.$model->getPath());
    }

    /**
     * Crée ou met à jour le fichier des relations d'un modèle.
     *
     * @param  Relations $relations
     * @param  bool      $preview
     * @return void
     */
    protected function generateModelRelations(Relations $relations, $preview)
    {
        // Fichier déjà existant : mise à jour des relations
        if (is_file($relations->getPath())) {
            if (!$relations->needsUpdate()) {
                return;
            }

            if (!$preview) {
                $relations->writeContent();
            }

            $this->line('<comment>Updated Relations:</comment> '.$relations->getName().' in '.$relations->getPath());
        }
        // Sinon : création du fichier
        else {
            if (!$preview) {
                $relations->writeContent();
            }

            $this->line('<info>Created Relations:</info> '.$relations->getName().' in '.$relations->getPath());
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
            ['table', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Create/update models of these tables only.'],
            ['preview', 'p', InputOption::VALUE_NONE, 'Displays results without touching files.'],
		];
	}
}
