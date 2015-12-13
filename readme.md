# Laravel Models Generator

Ce package permet la génération des modèles Eloquent à partir de la base de données.

## Installation

Inclure le package avec Composer :

```
composer require axn/laravel-models-generator
```

Ajouter le service provider au tableau des providers dans `config/app.php` :

```
'Axn\ModelsGenerator\ServiceProvider',
```

Copier le fichier de config et les templates (stubs) du package via la commande :

```
php artisan vendor:publish
```

Et renseigner les paramètres de config en fonction de l'application (chemins des
différents répertoires, tables pivots, noms forcés des modèles, etc.).

Si nécessaire, modifier les fichiers de templates (stubs) des modèles et relations.
**Attention dans ce cas aux tags de remplacement dans le template du modèle !**

## Utilisation

### Commande "generate"

Lance la génération/MAJ des modèles :

```
php artisan models:generate
```
