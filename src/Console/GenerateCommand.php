<?php

namespace Axn\ModelsGenerator\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Axn\ModelsGenerator\Generator;
use Axn\ModelsGenerator\Drivers\Driver;

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
     * Exécute la commande.
     *
     * @return void
     */
    public function handle()
    {
        $config = $this->laravel['config'];
        $ignoredTables = $config->get('models-generator.ignored_tables');

        $generators = Generator::initAndGetInstances($config, $this->getDriver());

        foreach ($generators as $generator) {
            if (in_array($generator->getTableName(), $ignoredTables)) continue;

            if ($generator->generateModel($updated)) {
                $this->line("Model <info>".$generator->getModelName()."</info> ".($updated ? "updated" : "generated"));
            } else {
                $this->error("Error while writing model ".$generator->getModelName());
            }
        }
    }

    /**
     * Retourne une instance du driver correspondant à la connexion par défaut
     * à la base de données.
     *
     * @return Driver
     */
    protected function getDriver()
    {
        $db = $this->laravel['db']->connection();

        $driverClass = '\Axn\ModelsGenerator\Drivers\\'.ucfirst($db->getDriverName()).'Driver';

        return new $driverClass($db->getPdo());
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
