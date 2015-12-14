# Laravel Models Generator

Ce package permet de générer les modèles Eloquent à partir de la base de données.

## Installation

Inclure le package avec Composer :

```
composer require axn/laravel-models-generator
```

Ajouter le service provider au tableau des providers dans `config/app.php` :

```
'Axn\ModelsGenerator\ServiceProvider',
```

Publier la config et les templates (stubs) du package via la commande :

```
php artisan vendor:publish
```

La config est publiée dans `config/models-generator.php`
Les templates sont publiés dans `resources/stubs/vendor/models-generator/`

Modifier si nécessaire les options de config et les templates des modèles et relations.
**Attention à ne pas toucher aux tags de remplacement dans le template du modèle !**

## Utilisation

Lancer simplement la commande :

```
php artisan models:generate
```
