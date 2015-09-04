<?php

namespace Axn\ModelsGenerator\Console;

use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ListCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'models:list';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Shows repositories alias/concretes list';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
        try {
            $modelsDir = $this->laravel['files']->allFiles(
                $this->laravel['config']->get('models-generator.models.dir')
            );
            $modelsNames = array_map(function($x) { return basename($x, '.php'); }, $modelsDir);
            $rows = [];

            foreach ($modelsNames as $alias) {
                if (!class_exists($alias)) {
                    throw new Exception("Cannot call $alias.");
                }
                elseif (!method_exists($alias, 'getFacadeRoot')) {
                    throw new Exception("$alias is not a facade alias.");
                }
                else {
                    $rows[] = [$alias, get_class($alias::getFacadeRoot())];
                }
            }

            $this->table(['Alias', 'Concrete'], $rows);
        }
        catch (Exception $e) {
            $this->info('Exception catched:');
            $this->comment($e->getMessage());
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
