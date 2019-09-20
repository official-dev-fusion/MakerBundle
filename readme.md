#Introduction :

Ce bundle est un outil de génération de code. Il s'inspire fortement du symfony/maker-bundle en ajoutant plusieurs fonctionnalités et plus de flexibilité au niveau de la configuration.

Il a été conçu pour générer du code facilement adaptable respectant une philosofie SCRUD (search, create, read, update et delete) à partir d'un entité Doctrine.

La commande df:make:config génère un fichier de configuration.yaml basé sur une entité donnée située dans App\Entity. Ce fichier servira par la suite à générer le code. Le fichier doit être personnalisé afin de généerer le code attendu. Il se retrouve dans le dossier config/scrud.

La commande df:make:scrud génère un contrôleur avancé à partir d'un fichier de configuration situé dans config/scrud. Ce contrôleur permet d'effectuer les cinq opérations de base sur un modèle.

* Search : Liste de tous les enregistrements, filtre, pagination et multi-sélection;
* Read : Affichage d'un enregistrement donné identifié par sa clé primaire;
* Create : Créer un nouvel enregistrement;
* Update : Édition d'un ou plusieurs enregistrement(s) existant(s);
* Delete : Supprimer un ou plusieurs enregistrement(s) existant(s);

## Fonctionnalités :
* Extraction des chaines de caractère à partir des vues et Génération de fichiers de traductions.
* Possibilité de personalisé le fichier de traductions généré dan la langue locale.
* Possibilité de remplacer les squelettes de templates afin de générer du code personalisé.
* Possibilité de créer plusieurs squelettes et de choisir dans le fichier de configuration lequel sera utilisé pour générer le code.
* Le squelettes par défaut utilise Bootstrap4 et JQuery dans les vues générées afin d'améliorer l'expérience visuelle.
* Configuration d'un sous-dossier afin de séparer correctement le code généré (Exemple : Controller/Back or Controller/Front).
* Configuration d'une sous route pour séparer les différentes parties de l'application (Exemple : admin/user/read).
* Possibilité de générer un Voter afin de gérer l'accès de chacune des actions SCRUD selon le rôle de l'utilisateur.
* Possibilité de choisir les actions SCRUD qui seront générés. Seulement l'action search est obligatoire.
* Possibilité de générer un filtre afin de rechercher dans chacun des attributs de type strin ou text de l'entité.
* Possibilité de générer une pagination dans laquelle l'utilisateur peut modifier le nombre d'élément par page directement dans le filtre de recherche.
* Possibilité de générer un formulaire permettant de sélectionner plusieurs éléments en même temps afin de lancer des actions multiple (Exemple : Suppression de plusieurs éléments d'un seule coup).
* Génération d'un gestionnaire d'entité afin de mieux structurer le code généré.
* Modification du repository lié à l'entité afin de créer des méthodes de recherche pour le filtre.

## Requirements
* Symfony flex with Symfony => 4.0.
* symfony/maker-bundle
