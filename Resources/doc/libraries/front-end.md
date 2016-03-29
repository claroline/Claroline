
# Front-end libraries

## MANDATORY
- angular
- angular-bootstrap
- angular-sanitize
- angular-ui-tinymce
- bootstrap
- font-awesome
- jquery (util uniquement pour jQuery UI)
- jquery-ui (droppable, draggable, etc...)
- pdfjs-dist (player de PDF géré par Mozilla)
- tinymce-codemirror (coloration synthaxique dans TinyMCE)
- angular-gridster (système de grille pour les widgets)
- angular-ui-tree (Path / Organization / Cursus)
- angular-ui-select (Sélecteur avancé avec Recherche/Filtre / Module de recherche dans le CoreBundle)
- fullcalendar
- angular-bootstrap-colorpicker
- angular-touch (développé par Google / évènement des touchscreens)
- angular-resource (utilisé dans PortfolioBundle / WebsiteBundle)


## OURS
- angular-ui-resource-picker
- angular-ui-translation
- claroline-tinymce-accordion": "https://github.com/iCAPLyon1/tinymceAccordionPlugin.git#192df9058a", (util ? maintenu ?)
- claroline-tinymce-mention": "https://github.com/iCAPLyon1/tinymceMentionPlugin.git#f251a65c23", (Qu'est ce que c'est ?)
- angular-data-table (fonctionnalités pour les tableaux / bugs corrigés par Nico dans un fork / encore buggué)


## COSMETIC
- angular-animate
- select2 (remplace les select par une alternative jQuery)
- angular-motion
 

## DUPLICATES
- angular-ui-router
- angular-route
- angular-daterangepicker
- bootstrap-datepicker
- eonasdan-bootstrap-datetimepicker (version différente)
- eonasdan-bootstrap-datetimepicker (version différente)
- bootstrap-daterangepicker
- Datejs (calcul sur les Dates "sucre synthaxique")
- moment


## DEBATABLE
- angular-breadcrumb (fonctionne uniquement si on a UIRouter)
- ng-file-upload (upload de fichiers en AJAX, utilisé dans WebsiteBundle)
- fileapi (upload de fichier en AJAX) **Peut-être accompli avec FormData**


## TO REMOVE WITH REFACTORING
- backbone (gestionnaire de ressource va disparaitre au profit d'angular)
- underscore (gestionnaire de ressource va disparaitre au profit d'angular)

- angular-toArrayFilter (certainement utilisé, mais ça ne vaut pas le coup d'avoir une dépendance pour un filtre)
=> écrire le filtre nous même

- ngBootbox (modales de confirmation Angular / Utilisé par ExoBundle)
- confirm-bootstrap": "https://github.com/maxailloud/confirm-bootstrap.git#d436ee942d" (dans Portfolio ou Website )
=> A discuter : écrire notre système de modales

- jqPlot (graphiques de la Docimology de ExoBundle)
=> Trouver une librairie de graphique plus complète
=> Etudier FVANCOP/ChartNew (fork de ChartJS). Voir l'intégration avec AngularJS

- gridstack (système de grille)
=> En cours de remplacement dans les Widgets par angular-gridster

- jquery-option-tree (plugin jQuery / système de select en "cascade" / Utilisé dans Badges)
=> a enlever lors du passage à Angular JS

- jquery-sortable (plugin jquery / système de sort sous forme d'arbre)
=> a enlever lors du passage à Angular JS

- typeahead.js / typeahead.js-bootstrap3.less (autocomplete field / Utilisé dans les Tags)
=> à enlever/remplacer lors du passage à AngularJS

- tablesorter (plugin jQuery)
=> sera remplacer par angular-data-tables

- tag-it (plugin jQuery / ajoute des Tags dans un champ input / Utilisé dans le BlogBundle)
=> a enlever lors du passage à Angular JS
=> peut être doublon avec typeahead

- toastr (système de notification basé sur celui d'Android / Utilisé dans Portfolio)
=> doublon avec les Alerts bootstrap

- angular-strap (Doublon d'Angular Bootstrap / Utilisé dans PortfolioBundle, WebsiteBundle)

- mjolnic-bootstrap-colorpicker (couleur de fond des WS / Widgets)
=> utilisé angular-bootstrap-colorpicker

## TO REMOVE WITH VERIFICATION
- bootstrap-additions (réécrit / ajoute des élément de Bootstrap / Utilisé dans Website)


## TO REMOVE
- fluidvids
- system.js
- bootstrapaccessibilityplugin (ajoute des fonctionnalités d'accessibilité dans les components Bootstrap / Non utilisé)
- jquery.cookie (Non utilisé)
