import {CatalogTab} from '#/plugin/cursus/home/catalog/components/tab'

export default {
  name: 'training_catalog',
  icon: 'fa fa-fw fa-graduation-cap',
  class: 'Claroline\\CursusBundle\\Entity\\Home\\TrainingCatalogTab',
  context: ['home', 'desktop'],
  component: CatalogTab
}
