Changelog for Laravel Models Generator
======================================

3.0.0-dev
---------

- Ajout de la possibilité d'ignorer des relations (option de config "ignored_relations").
- Utilisation des noms de tables au lieu des noms de modèles pour les nommages au pluriel.
- Utilisation du mot-clé "Via" à la place de "Of" pour les précisions des relations.
- Suppression de l'appel à la méthode "withTimestamps" dans le template de la relation "belongsToMany".
- Ordre des méthodes des relations par nom de méthode et non par nom de table.
- Utilisation de l'IoC pour instancier le driver.

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
