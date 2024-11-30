Changelog
=========

6.6.1 (2024-11-30)
------------------

 - Prevent Pint updating generated files


6.6.0 (2023-02-21)
------------------

- Add support for Laravel 10


6.5.0 (2022-02-14)
------------------

- Add support for Laravel 9


6.4.0 (2022-01-05)
------------------

- Uses doctrine/dbal instead of custom driver for getting DB schema information


6.3.0 (2021-10-29)
------------------

- Update default model stub for GeneratedRelations trait
- Highlighting of the warning message in the generated traits
- Config comments in Laravelish style


6.2.0 (2020-09-25)
------------------

- Add support for Laravel 8


6.1.0 (2020-03-04)
------------------

- Add support for Laravel 7


6.0.1 (2020-01-23)
------------------

- Use Illuminate\Support\Str class instead of helpers


6.0.0 (2019-12-31)
------------------

- Add support for Laravel 6
- Drop support for Laravel 5.7 and older
- Drop support for "belongs to many" relationships
- Drop support for Polymorphic relationships
- English translation of readme, config and comments in stubs
- Added detection of the presence of timestamps (created_at and updated_at)
- Ability to ignore relationships globally


5.4.0 (2019-03-07)
------------------

- Add support for Laravel 5.8


5.3.0 (2019-01-07)
------------------

- Support du groupement automatique de modèles via préfixe sur les tables.
- Possibilité d'ignorer la détection automatique d'un pivot.


5.2.1 (2018-09-18)
------------------

- Add missing Laravel extra in composer.json


5.2.0 (2018-09-07)
------------------

- Add Laravel 5.7.* support


5.1.0 (2018-07-04)
------------------

- Add Laravel 5.5.* and 5.6.* support


5.0.0 (2018-06-05)
------------------

- Générations des relations dans des traits et non plus directement dans les modèles.
- Retour de la possibilité d'indiquer les clés étrangères à utiliser pour un pivot.
- Retrait de l'option de commande --update (-u).


4.1.0 (2018-01-18)
------------------

- Templates des relations : syntaxe PHP 5.5 pour les classes des modèles liés.
- Les relations vers les tables ignorées ne sont plus générées.
- Ajout de l'option de config "update\_existing\_models".


4.0.0 (2017-11-27)
------------------

- Détection automatique des tables pivot qui ont le mot clé "\_has\_".
- Suffixage des relations belongs-to-many avec une précision si le nom du pivot n'est pas standard.
- Retrait de la possibilité d'indiquer les clés étrangères à utiliser pour un pivot.
- Retrait de l'option de config "forced_names".
- Ajout de l'option de config "singular_rules".
- Ajout de l'option --preview (-p) à la commande.
- Tri des relations de manière globale et non plus par type de relation.
- Les relations has-many vers les tables pivot ne sont plus préfixées par le mot clé "pivot".
- Modification des noms de variables dans les templates.
- Exception levée si une erreur survient lors de l'initialisation de la génération.
- Correction de la détection des différences dans les relations des modèles pour la mise à jour.
- Refactoring complet du code.


3.1.0 (2017-02-01)
------------------

- Laravel 5.4.x support


3.0.1 (2016-11-02)
------------------

- Move to Github


3.0.0 (2016-05-13)
------------------

- Ajout des tables "cache" et "sessions" dans l'option de config "ignored_tables".
- Ajout de l'option de config "ignored_relations" pour exclure des relations de la génération.
- Ajout de l'option de commande "--table" ("-t") pour spécifier les tables à générer.
- Ajout de l'option de commande "--update" ("-u") pour mettre à jour les modèles existants.
- Renommage de l'option de config "groups" en "groupings".
- Modification du format des options de config "polymorphic_relations" et "one_to_one_relations".
- Utilisation des noms de tables au lieu des noms de modèles pour les nommages au pluriel.
- Utilisation du mot-clé "Via" à la place de "Of" pour les précisions des relations.
- Ajout du préfixe "pivot" aux noms des relations "has many" vers les tables pivots.
- Suppression de l'extention d'Eloquent dans le template du modèle.
- Suppression de l'appel à la méthode "withTimestamps" dans le template de la relation "belongsToMany".
- Notification de la MAJ d'un modèle uniquement si la MAJ est effective.
- Ordre des méthodes des relations par nom de méthode et non par nom de table.
- Amélioration de la gestion des erreurs.
- Utilisation de l'IoC pour instancier le driver.
- Réécriture des commentaires dans la config.


2.0.3 (2016-03-22)
------------------

- Source code released with the MIT license
- Added license file


2.0.2 (2016-01-14)
------------------

- Valeurs par défaut pour les récupérations des options de config.
- Déplacement des options de config obligatoires au début du fichier de config.


2.0.1 (2016-01-04)
------------------

- Changement du message d'information en console avec lien complet vers le modèle généré.

2.0.0 (2015-12-23)
------------------

- Abandon du support des repositories, contrats et façades.
- Ajout du support des relations hasOne et morphOne (option de config "one_to_one_relations").
- Ajout de la possibilité de renseigner les clés concernées par un pivot dans la config.
- Ajout de l'extention d'Eloquent au template du modèle.
- Option de config "polymorphic_tables" renommée "polymorphic_relations".
- Modification de l'option de config "groups" : 'table' => 'groupe' au lieu de 'groupe' => [liste_tables]
- Suppression de l'option de config "pivot_tables_group".
- Suppression de l'option de config "templates_dir" (chemin des templates en dur dans le code).
- Suppression de la commande "models:list".
- Suppression des nommages "parent" et "children" pour les relations.
- Les templates des relations peuvent maintenant être surchargés.
- Les templates sont copiés dans le dossier spécifié dans la config lors de l'exécution de la commande "vendor:publish".
- Déplacement et renommage du dossier et des fichiers des templates.
- Modification des commentaires dans les templates et la config.
- Gestion des erreurs de génération avec un try/catch.
- Modification des tags pour la commande "vendor:publish".
- Refactoring/nettoyage du code.


1.1.1 (2015-11-23)
------------------

- L'option de config "generate" dans "repositories", "contracts" et "facades" est à FALSE par défaut.
- Création des dossiers des groupes lors de la génération et non à l'instantiation du générateur.
- Commande "models:list" : affichage de la trace si exception catched.
- Nettoyage commentaires.


1.1.0 (2015-10-26)
------------------

- Suppression de la détection automatique des relations polymorphiques.
- Ajout d'une option de config pour définir les relations polymorphiques.
- Les templates des contrats et façades peuvent maintenant être surchargés.


1.0.1 (2015-10-07)
------------------

- Commande "models:list" : vérification que chaque fichier trouvé contient bien une classe modèle (= instanciable).
- Changements mineurs au niveau des couleurs en console.


1.0.0 (2015-09-08)
------------------

- First release.
