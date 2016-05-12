<?php

namespace Axn\ModelsGenerator\Console;

use Exception;
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
    protected $description = 'Generates Eloquent models files from DB';

    /**
     * Exécute la commande.
     *
     * @return void
     */
    public function handle()
    {
        $config = $this->laravel['config'];
        $driver = $this->laravel['Axn\ModelsGenerator\Drivers\Driver'];
        $tables = $this->option('table');
        $update = $this->option('update');

        try {
            $generators = Generator::initAndGetInstances($config, $driver, $this);

            foreach ($generators as $generator) {
                if (empty($tables) || in_array($generator->getTableName(), $tables)) {
                    $generator->generateModel($update);
                }
            }
        }
        catch (Exception $e) {
            $this->error('Exception catched: '.$e->getMessage());
            $this->line($e->getTraceAsString());
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
		];
	}
}
