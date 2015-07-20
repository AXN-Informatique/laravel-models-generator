## Laravel Models Generator

Ce package permet la génération des modèles et repositories (implémentations Eloquent,
interfaces et façades) à partir de la base de données.

## Installation

Inclure le package avec Composer :

```
composer require axn/laravel-models-generator
```

Ajouter le service provider au tableau des providers dans config/app.php :

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

Pour lancer la génération des modèles et repositories, utiliser la commande :

```
php artisan models:generate
```

Il est possible de désactiver des générations via le paramètre "generate" pour chacun
des types de fichiers (modèles, repositories, contrats et façades). Exemple si l'on
ne souhaite pas générer les façades :

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
