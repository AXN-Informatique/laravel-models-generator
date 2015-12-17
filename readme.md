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

Publier si besoin la config et les templates (stubs) du package via les commandes :

```
// config
php artisan vendor:publish --tag=config --provider=Axn\\ModelsGenerator\\ServiceProvider

// stubs
php artisan vendor:publish --tag=stubs --provider=Axn\\ModelsGenerator\\ServiceProvider
```

La config est publiée dans `config/models-generator.php`
Les templates sont publiés dans `resources/stubs/vendor/models-generator/`

Modifier au besoin les options de config et les templates des modèles et relations.
**Attention à ne pas toucher aux tags de remplacement dans le template du modèle !**

## Utilisation

Lancer simplement la commande :

```
php artisan models:generate
```
