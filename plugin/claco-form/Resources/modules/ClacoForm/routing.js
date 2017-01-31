import menuTemplate from './Partial/menu.html'
import generalConfigurationTemplate from './Partial/general_configuration.html'
import fieldsManagementTemplate from './Partial/fields_management.html'
import categoriesManagementTemplate from './Partial/categories_management.html'
import keywordsManagementTemplate from './Partial/keywords_management.html'
import templateManagementTemplate from './Partial/template_management.html'
import entriesListTemplate from './Partial/entries_list.html'
import entryFormTemplate from './Partial/entry_form.html'
import entryViewTemplate from './Partial/entry_view.html'

export default function ($stateProvider, $urlRouterProvider) {
  const config = window['resourceDetails']

  $stateProvider
    .state ('menu', {
      url: '/menu',
      template: menuTemplate,
      controller: 'MenuCtrl',
      controllerAs: 'cfc'
    })
    .state ('general_configuration', {
      url: '/configuration',
      template: generalConfigurationTemplate,
      controller: 'GeneralConfigurationCtrl',
      controllerAs: 'cfc'
    })
    .state ('fields_management', {
      url: '/fields/management',
      template: fieldsManagementTemplate,
      controller: 'FieldsManagementCtrl',
      controllerAs: 'cfc'
    })
    .state ('categories_management', {
      url: '/categories/management',
      template: categoriesManagementTemplate,
      controller: 'CategoriesManagementCtrl',
      controllerAs: 'cfc'
    })
    .state ('keywords_management', {
      url: '/keywords/management',
      template: keywordsManagementTemplate,
      controller: 'KeywordsManagementCtrl',
      controllerAs: 'cfc'
    })
    .state ('template_management', {
      url: '/template/management',
      template: templateManagementTemplate,
      controller: 'TemplateManagementCtrl',
      controllerAs: 'cfc'
    })
    .state ('entries_list', {
      url: '/entries/list',
      template: entriesListTemplate,
      controller: 'EntriesListCtrl',
      controllerAs: 'cfc'
    })
    .state ('entry_creation', {
      url: '/entries/creation',
      template: entryFormTemplate,
      controller: 'EntryCreationCtrl',
      controllerAs: 'cfc'
    })
    .state ('entry_edition', {
      url: '/entries/{entryId}/edition',
      template: entryFormTemplate,
      controller: 'EntryEditionCtrl',
      controllerAs: 'cfc'
    })
    .state ('entry_view', {
      url: '/entries/{entryId}/view',
      template: entryViewTemplate,
      controller: 'EntryViewCtrl',
      controllerAs: 'cfc'
    })
    .state ('entry_random', {
      url: '/entries/random',
      controller: 'EntryRandomCtrl',
      controllerAs: 'cfc'
    })

  switch (config['default_home']) {
    case 'random' :
      $urlRouterProvider.otherwise('/entries/random')
      break
    case 'search' :
      $urlRouterProvider.otherwise('/entries/list')
      break
    case 'add' :
      $urlRouterProvider.otherwise('/entries/creation')
      break
    default :
      $urlRouterProvider.otherwise('/menu')
  }
}
