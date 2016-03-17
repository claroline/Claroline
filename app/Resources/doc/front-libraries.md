# INDISPENSABLE

angular
angular-bootstrap
angular-sanitize
angular-ui-tinymce
backbone (gestionnaire de ressource va disparaitre au profit d'angular)
underscore (gestionnaire de ressource va disparaitre au profit d'angular)
bootstrap
font-awesome
jquery (util uniquement pour jQuery UI)
jquery-ui (droppable, draggable, etc...)
pdfjs-dist (player de PDF géré par Mozilla)
tinymce-codemirror (coloration synthaxique dans TinyMCE)
angular-gridster (système de grille pour les widgets)
angular-ui-tree (Path / Organization / Cursus)
angular-ui-select (Sélecteur avancé avec Recherche/Filtre / Module de recherche dans le CoreBundle)
fullcalendar
mjolnic-bootstrap-colorpicker (couleur de fond des WS / Widgets)

# COSMETIC
angular-animate
select2 (remplace les <select> par une alternative jQuery)
angular-motion
 
# DUPLICATES
angular-ui-router
angular-route

angular-daterangepicker
bootstrap-datepicker
eonasdan-bootstrap-datetimepicker (version différente)
eonasdan-bootstrap-datetimepicker (version différente)
bootstrap-daterangepicker

Datejs (calcul sur les Dates "sucre synthaxique")
moment


# TENDANCIEUX
angular-breadcrumb (fonctionne uniquement si on a UIRouter)
ng-file-upload (upload de fichiers en AJAX, utilisé dans WebsiteBundle)
jquery.cookie (?)


# TO REMOVE
angular-resource
fluidvids

# TO REMOVE WITH REFACTORING
angular-toArrayFilter (certainement utilisé, mais ça ne vaut pas le coup d'avoir une dépendance pour un filtre)
=> écrire le filtre nous même

ngBootbox (modales de confirmation Angular / Utilisé par ExoBundle)
confirm-bootstrap": "https://github.com/maxailloud/confirm-bootstrap.git#d436ee942d" (dans Portfolio ou Website )
=> A discuter : écrire notre système de modales

jqPlot (graphiques de la Docimology de ExoBundle)
=> Trouver une librairie de graphique plus complète

gridstack (système de grille)
=> En cours de remplacement dans les Widgets par angular-gridster

jquery-option-tree (plugin jQuery / système de select en "cascade" / Utilisé dans Badges)
=> a enlever lors du passage à Angular JS

jquery-sortable (plugin jquery / système de sort sous forme d'arbre)
=> a enlever lors du passage à Angular JS

fileapi (upload de fichier en AJAX)
=> Peut-être accompli avec FormData

# A NOUS
angular-ui-resource-picker
angular-ui-translation
claroline-tinymce-accordion": "https://github.com/iCAPLyon1/tinymceAccordionPlugin.git#192df9058a", (util ? maintenu ?)
claroline-tinymce-mention": "https://github.com/iCAPLyon1/tinymceMentionPlugin.git#f251a65c23", (Qu'est ce que c'est)
angular-data-table (fonctionnalités pour les tableaux / bugs corrigés par Nico dans un fork / encore buggué)


A TRIER
angular-strap
angular-touch

bootstrap-additions
bootstrapaccessibilityplugin


system.js
tablesorter (plugin jQuery)
tag-it
toastr
typeahead.js
typeahead.js-bootstrap3.less

