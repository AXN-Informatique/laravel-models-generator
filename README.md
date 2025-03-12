Laravel Models Generator
========================

Generates Eloquent models and relations (belongs to, has many, has one) using DB schema.

Installation
------------

Install the package with Composer:

```sh
composer require axn/laravel-models-generator
```

In Laravel 5.5 the service provider will automatically get registered.
In older versions of the framework just add the service provider
to the array of providers in `config/app.php`:

```php
'providers' => [
    // ...
    Axn\ModelsGenerator\ServiceProvider::class,
],
```

Publish config and templates (stubs) if needed using these commands:

```sh
// config
php artisan vendor:publish --tag=models-generator.config

// stubs
php artisan vendor:publish --tag=models-generator.stubs
```

Config is published in `config/models-generator.php`
Templates are published in `resources/stubs/vendor/models-generator/`

Modify config options and templates contents if needed.

Usage
-----

Simply launch this command:

```
php artisan models:generate
```

**Options :**

* **--table (ou -t) :** if you want to generate only certain tables. To select
  many tables, you can do: -t table1 -t table2 -t ...
* **--preview (ou -p) :** if you want to only display information messages about
  actions that will be done by the generator but without touching files.

Naming convention
-----------------

- **Model:** table name in singular and studly case (cf config "singular_rules"
  if singularization is not done correctly).
- **"has many" relation:** name of the related table (plural), in camel case.
- **"has one" relation:** name of the related table (singular), in camel case
- **"belongs to" relation:** foreign key name (without "\_id" or "id\_"), in camel
  case.

**Other details:**

- **"has many" relation:** the relation name is simply the model name in camel case,
  so the model name should be in plural.
- **"has many" and "has one" relations:** if the foreign key name is not standard,
  a precision is concatenated to the relation name like "Via{nomFK}".

Example:

```php
// "comments" table
class Comment extends Model
{
    // "belongs to" relation to "users" via "user_id"
    public function user() {}

    // "belongs to" relation to "users" via "updator_id"
    public function updator() {}
}

// "users" table
class User extends Model
{
    // "has many" relation to "comments" via "user_id"
    public function comments() {}

    // "has many" relation to "comments" via "updator_id"
    public function commentsViaUpdatorId() {}
}
```
