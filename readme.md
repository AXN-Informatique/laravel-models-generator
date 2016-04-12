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
php artisan vendor:publish --tag=models-generator.config

// stubs
php artisan vendor:publish --tag=models-generator.stubs
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

## Conventions de nommage

- **Modèle** = nom table singularisé (voir option de config "forced_names" si la singularisation
  ne se fait pas correctement) + studly case.
- **Relations "has many", "morph many" et "belongs to many"** = nom table liée + camel case.
- **Relations "has one" et "morph one"** = nom modèle + camel case.
- **Relation "belongs to"** = nom foreign key (sans le "_id") + camel case.
- **Relation "morph to"** = nom morph défini dans l'option de config "polymorphic_relations".

Concernant les relations "has many", "morph many" et "belongs to many", il est à noter
que les tables doivent être nommées au pluriel pour que ces relations soient correctement
nommées (pluralisation non réalisée par le générateur). De plus, si le nom de la foreign key
ne correspond pas au nom de la table, une précision sera ajoutée au nom de la relation, sous
la forme "Via{nomFK}".

Exemple :

```php
// Modèle table "comments"
class Comment extends Model
{
    // Relation "belongs to" vers la table "users" via fk "user_id"
    public function user() {}

    // Relation "belongs to" vers la table "users" via fk "updator_id"
    public function updator() {}
}

// Modèle table "users"
class User extends Model
{
    // Relation "has many" vers la table "comments" via fk "user_id"
    public function comments() {}

    // Relation "has many" vers la table "comments" via fk "updator_id"
    public function commentsViaUpdator() {}
}
```
