# Changelog for Laravel Models Generator

## 1.1.1 (2015-11-23)

- L'option de config "generate" dans "repositories", "contracts" et "facades" est à FALSE par défaut.
- Création des dossiers des groupes lors de la génération et non à l'instantiation du générateur.
- Commande "models:list" : affichage de la trace si exception catched.
- Nettoyage commentaires.

## 1.1.0 (2015-10-26)

- Suppression de la détection automatique des relations polymorphiques.
- Ajout d'une option de config pour définir les relations polymorphiques.
- Les templates des contrats et façades peuvent maintenant être surchargés.

## 1.0.1 (2015-10-07)

- Commande "models:list" : vérification que chaque fichier trouvé contient bien une classe modèle (= instanciable).
- Changements mineurs au niveau des couleurs en console.

## 1.0.0 (2015-09-08)

- First release.
