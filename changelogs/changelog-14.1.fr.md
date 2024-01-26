# Nouvelles ressources / fonctionnalités :
- (Flashcards) Cartes Mémoire : Une nouvelle ressource basée sur le système Leitner et permettant la création de listes de cartes à mémoriser par les apprenants.
- Export SCORM : cette nouvelle fonctionnalité ajoutée dans la version de Claroline distribuée par Forma-Libre permet à tout utilisateur d'exporter un espace d'activité en tant que paquet SCORM. Le SCORM est ensuite importable dans un autre LMS afin de faciliter la diffusion de contenu entre les plateformes. 
- Ajout d'une vue statistique sur les ressources base de Donnée et possibilité de les exporter. La ressource devient donc le choix à privilégier pour réaliser des sondages et autres formulaires.

# Changements majeurs :
- Changements des procédures d'inscription : fusion de deux actions pour simplifier le processus. 
- Ajout d'un utilisateur à un espace, l'ajoute automatiquement au rôle par défaut (il faut cocher une case pour avoir le choix du rôle)
- Ajout d'options pour paramétrer l'accès aux champs confidentiels sur les formulaires personnalisés d'inscriptions aux formations, les profils et les fiches de base de données
- Simplification des présences dans cursus
- Nouvel import pour vider tous les groupes / utilisateurs inscrit à un E.A.
- Refonte de l'API : normalisation des url
    - Suppression du support des ID auto-increment :
    Les ids exposés par l'API dans la propriété `autoId` ne peuvent plus être utilisé pour appeler l'API. Il faut utiliser l'UUID (propriété id) ou les autres identifiants de l'objet (par exemple, le code d'un espace d'activités, l'email d'un utilisateur).
    - Suppression du endpoint /exist pour tous les objets :
14.0
```
GET /apiv2/{entityName}/exist/{field}/{value}
exemple : /apiv2/user/exist/id/e12b480d-c934-4839-b0d0-c4805ffe5012
```
14.1
```
HEAD /apiv2/{entityName}/{field}/{value}
exemple : /apiv2/user/id/e12b480d-c934-4839-b0d0-c4805ffe5012
```
- 
    - Suppression du endpoint /find pour tous les objets :
        - Pour les recherches via un identifiant, utiliser le endpoint `GET/HEAD /apiv2/{entityName}/{field}/{value}`
        - Pour une recherche via filtres, utiliser le endpoint de list `GET /apiv2/{entityName}` avec `filters[myFilter]`  dans la query string.
        
Pour rappel la liste des endpoints de l'API est disponible dans Administration > Intégration > API Claroline.

# Changements mineurs :
- Miniature automatiques sur ressources peertube et youtube
- l'option "cacher" passe dans "Affichage" au lieu de "Restriction d'accès"
- Affichage d'une bannière dans les sujets de forum
- Nouveaux widgets Liste :
    - Liste des équipes de l'espace et de "Mes équipes"
    - Liste des groupes et de "Mes groupes"
    - Liste des rôles et de "Mes rôles"
    - Liste des membres de mes équipes (avec un filtre sur le nom des équipes) 
- Un espace lié à une formation affiche la page formation au lieu d'un message générique si on n'est pas encore inscrit à l'espace.
- Ajout du choix du template sur session, séance dans cursus
- Ajout d'un champ de Base de Donnée (dans Ressources et Formations) permettant d'afficher un bloc de texte fixe au sein d'un formulaire pour en améliorer la mise en forme et le guidage.
