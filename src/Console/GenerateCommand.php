<?php

namespace Axn\ModelsGenerator\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Axn\ModelsGenerator\Generator;

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
    protected $description = 'Generates models/repositories files';

    /**
     * Exécute la commande.
     *
     * @return void
     */
    public function handle()
    {
        $db = $this->laravel['db']->connection();
        $driverClass = '\Axn\ModelsGenerator\Drivers\\'.ucfirst($db->getDriverName()).'Driver';
        $driver = new $driverClass($db->getPdo());

        $generators = Generator::initGenerators($this->laravel['config'], $driver);
        $ignored = $this->laravel['config']->get('models-generator.ignored_tables', []);

        foreach ($generators as $generator) {
            if (!in_array($generator->getTableName(), $ignored)) {
                $this->callGenerationMethods($generator);
            }
        }
    }

    /**
     * Appelle les différentes méthodes de génération du générateur.
     *
     * @param  Generator $generator
     * @return void
     */
    protected function callGenerationMethods(Generator $generator)
    {
        $config = $this->laravel['config'];

        // Génération/m.a.j du modèle
        if ($config->get('models-generator.models.generate')) {
            if ($generator->generateModel($updated)) {
                $this->line("Model <info>".$generator->getModelName()."</info> ".($updated ? "updated" : "generated"));
            } else {
                $this->error("Error while writing model ".$generator->getModelName());
            }
        }

        // Génération du repository s'il n'existe pas
        if ($config->get('models-generator.repositories.generate')
            && !is_file($generator->getRepositoryPath())) {

            if ($generator->generateRepository()) {
                $this->line("Repository <info>".$generator->getRepositoryName()."</info> generated");
            } else {
                $this->error("Error while writing repository ".$generator->getRepositoryName());
            }
        }

        // Génération du contrat si le repository existe
        if ($config->get('models-generator.contracts.generate')
            && is_file($generator->getRepositoryPath())) {

            if ($generator->generateContract()) {
                $this->line("Contract <info>".$generator->getContractName()."</info> generated");
            } else {
                $this->error("Error while writing contract ".$generator->getContractName());
            }
        }

        // Génération de la façade si celle-ci n'existe pas déjà et si le contrat existe
        if ($config->get('models-generator.facades.generate')
            && is_file($generator->getContractPath())
            && !is_file($generator->getFacadePath())) {

            if ($generator->generateFacade()) {
                $this->line("Facade <info>".$generator->getFacadeName()."</info> generated");
            } else {
                $this->error("Error while writing facade ".$generator->getFacadeName());
            }
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
			//['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}
}
