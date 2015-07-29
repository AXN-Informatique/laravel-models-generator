<?php

namespace Axn\ModelsGenerator\Console;

use Illuminate\Console\Command;
use Illuminate\Config\Repository as Config;
use Illuminate\Database\DatabaseManager as Db;
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
	protected $description = "Generates models and repositories files.";

    /**
     * Instance de la config Laravel.
     *
     * @var Config
     */
    protected $config;

    /**
     * Instance du manager de base de données de Laravel.
     *
     * @var Db
     */
    protected $db;

    /**
     *
     * @param Config $config
     * @param Db     $db
     */
    public function __construct(Config $config, Db $db)
    {
        $this->config = $config;
        $this->db = $db;

        parent::__construct();
    }

	/**
	 * Exécute la commande.
	 *
	 * @return void
	 */
	public function fire()
	{
        $db = $this->db->connection();
        $driverClass = '\Axn\ModelsGenerator\Drivers\\'.ucfirst($db->getDriverName()).'Driver';
        $driver = new $driverClass($db->getPdo());

        $generators = Generator::initGenerators($this->config, $driver);
        $ignored = $this->config->get('models-generator.ignored_tables', []);

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
        // Génération/m.a.j du modèle
        if ($this->config->get('models-generator.models.generate')) {
            $this->info($generator->generateModel());
        }

        // Génération du repository s'il n'existe pas
        if ($this->config->get('models-generator.repositories.generate')
            && !is_file($generator->getRepositoryPath())) {

            $this->info($generator->generateRepository());
        }

        // Génération du contrat si le repository existe
        if ($this->config->get('models-generator.contracts.generate')
            && is_file($generator->getRepositoryPath())) {

            $this->info($generator->generateContract());
        }

        // Génération de la façade si celle-ci n'existe pas déjà et si le contrat existe
        if ($this->config->get('models-generator.facades.generate')
            && is_file($generator->getContractPath())
            && !is_file($generator->getFacadePath())) {

            $this->info($generator->generateFacade());
        }
    }
}
