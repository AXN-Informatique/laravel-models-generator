<?php

namespace Axn\ModelsGenerator\Console;

use Exception, ReflectionClass;
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
            $modelsFiles = $this->laravel['files']->allFiles(
                $this->laravel['config']->get('models-generator.models.dir')
            );
            $modelsNs = $this->laravel['config']->get('models-generator.models.ns');
            $rows = [];

            foreach ($modelsFiles as $file) {
                $name  = $file->getBasename('.php');
                $ns    = $modelsNs.($file->getRelativePath() ? '\\'.str_replace('/', '\\', $file->getRelativePath()) : '');
                $class = $ns.'\\'.$name;

                if (!(new ReflectionClass($class))->isInstantiable()) {
                    $this->line('<info>Note:</info> '.$class.' ignored (not instantiable)');
                    continue;
                }

                if (!class_exists($name)) {
                    throw new Exception("Cannot call $name.");
                }
                elseif (!method_exists($name, 'getFacadeRoot')) {
                    throw new Exception("$name is not a facade alias.");
                }
                else {
                    $rows[] = [$name, get_class($name::getFacadeRoot())];
                }
            }

            $this->table(['Alias', 'Concrete'], $rows);
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
			//['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}
}
