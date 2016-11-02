Laravel Models Generator
========================

Ce package permet de générer les modèles Eloquent à partir de la base de données.

Installation
------------

Inclure le package avec Composer :

```sh
composer require axn/laravel-models-generator
```

Ajouter le service provider au tableau des providers dans `config/app.php` :

```php
'Axn\ModelsGenerator\ServiceProvider',
```

Publier si besoin la config et les templates (stubs) du package via les commandes :

```sh
// config
php artisan vendor:publish --tag=models-generator.config

// stubs
php artisan vendor:publish --tag=models-generator.stubs
```

La config est publiée dans `config/models-generator.php`
Les templates sont publiés dans `resources/stubs/vendor/models-generator/`

Modifier au besoin les options de config et les templates des modèles et relations.
**Attention à ne pas toucher aux tags de remplacement dans le template du modèle !**

Utilisation
-----------

Lancer simplement la commande :

```
php artisan models:generate
```

**Options :**

* **--table (ou -t) :** Permet d'indiquer les tables à générer. Pour renseigner
  plusieurs tables, faire : -t table1 -t table2 -t ...
* **--update (ou -u) :** Si cette option est précisée, les modèles déjà existants
  seront mis à jour (relations).

## Conventions de nommage

- **Modèle** = nom table singularisé (voir option de config "forced_names" si la singularisation
  ne se fait pas correctement) + studly case.
- **Relations "has many", "morph many" et "belongs to many"** = nom table liée + camel case.
- **Relations "has one" et "morph one"** = nom modèle + camel case.
- **Relation "belongs to"** = nom foreign key (sans le "_id") + camel case.
- **Relation "morph to"** = nom morph défini dans l'option de config "polymorphic_relations".

**Concernant les relations "has many", "morph many" et "belongs to many"** :

- Pour que les noms de ces relations soient au pluriel, il faut que les noms des tables
  soient au pluriel (le générateur ne fait pas de pluralisation).
- Si la table liée est un pivot, le mot-clé "pivot" sera ajouté en préfixe à la relation.
- Si le nom de la foreign key ne correspond pas au nom de la table, une précision sera ajoutée
  au nom de la relation, sous la forme "Via{nomFK}".

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

    // Relation "has many" vers la table "role_user" qui est un pivot
    public function pivotRoleUser() {}
}
```

Limitations
-----------

### Noms de relations en doublon

Il se peut que deux relations aient le même nom dans un même modèle, par exemple
s'il y a une relation polymorphique ET une relation "has one or many" vers une même table.

Exemple :

```php
class Staff extends Model
{
    // Relation polymorphique vers la table "photos" via champs "imageable_type" et "imageable_id"
    public function photos()
    {
        return $this->morhMany('Photo', 'imageable');
    }

    // Relation "has many" vers la table "photos" via fk "staff_id"
    public function photos()
    {
        return $this->hasMany('Photo', 'staff_id');
    }
}
```

Dans ce cas il faut soit retirer la relation "has many", via l'option de config "ignored_relations",
soit retirer la relation polymorphique qui a été renseignée dans l'option de config "polymorphic_relations".
