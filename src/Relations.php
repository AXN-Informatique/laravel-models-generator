<?php

namespace Axn\ModelsGenerator;

use Axn\ModelsGenerator\Traits\HasStub;

class Relations
{
    use HasStub;

    /**
     * Nom de trait des relations.
     *
     * @var string
     */
    protected $name;

    /**
     * Namespace du trait.
     *
     * @var string
     */
    protected $namespace;

    /**
     * Chemin complet vers le fichier du trait.
     *
     * @var string
     */
    protected $path;

    /**
     * Liste des relations.
     *
     * @var array
     */
    protected $relations = [];

    /**
     * Constructeur.
     *
     * @param  string    $name
     * @param  string    $namespace
     * @param  string    $path
     * @return void
     */
    public function __construct($name, $namespace, $path)
    {
        $this->name = $name;
        $this->namespace = $namespace;
        $this->path = $path;
    }

    /**
     * Retourne le nom du trait.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Retourne le nom complet du trait.
     *
     * @return string
     */
    public function getTrait()
    {
        return $this->namespace.'\\'.$this->name;
    }

    /**
     * Retourne le chemin du trait.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Ajoute une relation si celle-ci n'existe pas déjà.
     *
     * @param  Relations\Relation $relation
     * @return void
     */
    public function add(Relations\Relation $relation)
    {
        if (isset($this->relations[$relation->getName()])) {
            throw new \Exception(
                'Relation '.$relation->getName().' is duplicated in model '.$relation->getParentModel()->getName()
            );
        }

        $this->relations[$relation->getName()] = $relation;
    }

    /**
     * Retourne le contenu généré.
     *
     * @return string
     */
    public function getContent()
    {
        ksort($this->relations);

        $relations = '';

        foreach ($this->relations as $name => $relation) {
            $relations .= $relation->getContent();
        }

        return strtr($this->getStubContent('relations'), [
            '{{namespace}}' => $this->namespace,
            '{{name}}'      => $this->name,
            '{{relations}}' => $relations,
        ]);
    }

    /**
     * Retourne le contenu du fichier existant.
     *
     * @return string
     */
    public function getFileContent()
    {
        if (!is_file($this->getPath())) {
            return '';
        }

        return file_get_contents($this->getPath());
    }

    /**
     * Écrit le contenu généré dans le fichier des relations.
     *
     * @return void
     */
    public function writeContent()
    {
        $dirPath = dirname($this->getPath());

        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0755, true);
        }

        file_put_contents(
            $this->getPath(),
            $this->getContent()
        );
    }

    /**
     * Indique si le fichier des relations nécessite d'être mis à jour.
     *
     * @return bool
     */
    public function needsUpdate()
    {
        $new = $this->getContent();
        $old = $this->getFileContent();

        $normalize = function($content) {
            return str_replace("\r\n", "\n", $content);
        };

        return $normalize($new) !== $normalize($old);
    }
}
