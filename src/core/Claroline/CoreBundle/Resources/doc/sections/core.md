[[Documentation index]][index_path]

[index_path]: ../index.md

DROITS

1) Stockage:

Les droits sur les ressources sont stockés dans l'entité Resource\ResourceRights.
Cette table possède un lien vers une ressource et un lien vers un rôle (une série de permission par ressource et par rôle).
Les permissions pouvant être mise à true/false sont: COPY, DELETE, EDIT, OPEN, VIEW.

Cette table possède également une relation N-N avec la table des types de ressources.
Les enregistrement de la table intermédiaire de cette relation indiquent les types de ressources que l'ont peut rajouter à l'intérieur.

2) Voter:

C'est un voter (ResourceVoter) qui va décider si un utilisateur peut ou non effectuer une action sur une ressource.
Il est appellé lorsqu'on utilise l'instruction "$this->get('security.context')->isGranted($action, $object);"

Actuellement, les actions possibles sont 'MOVE', 'COPY', 'DELETE', 'EXPORT', 'CREATE', 'EDIT', 'OPEN'

Le paramètre $object est en fait un objet de la classe ResourceCollection.

Cet $object peut également prendre des paramètres (setParameters).
Actuellement ces paramètres sont sous la forme d'une array dont les clefs sont 'parent' et 'type'.

- type représente le nom du type de ressource.
- parent est l'entité parente dans certains cas.

Ils ne sont obligatoire que de les cas suivants:

CREATE => type
MOVE => parent (le nouveau)
COPY => parent (le nouveau)

3) Création:

Les insert dans la table de droits se font pour la première fois dans lors de la création du workspace (workspace.creator) puisque
la répertoire racine sera créé à ce moment.
Les droits par défaut y sont actuellement inscrit en dur.

Lors de la création d'une ressource, les droits de la resource père (le père existera toujours puisqu'une ressource
est toujours dans un workspace qui aura toujours une racine) sont copiés pour la ressource fille.
Même chose lorsqu'une ressource est bougée ou copiée. En fait lorsqu'on effectue une opération sur une ressource qui peut modifier/créer son père,
elle en reprends les droits.

4) Visibilité:

Les ressources sont visibiles dans le gestionnaire de ressource si leur boolean canView est mit à true.
Les méthode getChildren() et filter() de AbstractResourceRepository sont capables de tenir compte de ce paramètre
et ne renvoyer que la liste de ressources visibles pour un utilisateur donné.
