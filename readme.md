# Laravel Models Generator

Ce package permet la génération des modèles et repositories (implémentations Eloquent,
interfaces et façades) à partir de la base de données.

## Installation

Inclure le package avec Composer :

```
composer require axn/laravel-models-generator
```

Ajouter le service provider au tableau des providers dans `config/app.php` :

```
'Axn\ModelsGenerator\ServiceProvider',
```

Copier le fichier de config du package vers la config local via la commande :

```
php artisan vendor:publish
```

Et renseigner les paramètres de config en fonction de l'application (chemins des
différents répertoires, tables pivot, noms forcés des modèles, etc.).

## Utilisation

### Commande "generate"

Lance la génération des modèles et repositories :

```
php artisan models:generate
```

Il est possible de désactiver des générations via le paramètre `generate` dans la
config pour chacun des types de fichiers (modèles, repositories, contrats et façades).
Exemple si l'on ne souhaite pas générer les façades :

```php
[
    'facades' =>  [
        'generate' => false
    ]
]
```

Il est également possible d'utiliser ses propres templates pour les modèles et repositories
en les ajoutant au répertoire des templates défini dans la config. Attention dans
ce cas aux tags de remplacement !

### Commande "list"

Affiche la liste des alias bindés aux repositories (ce qui permet dans le même temps
de vérifier si pour chaque modèle il existe bien un repository et si celui-ci est
bien associé à un alias) :

```
php artisan models:list
```
