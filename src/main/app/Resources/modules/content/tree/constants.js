import {trans} from '#/main/app/intl/translation'

import {DataTree} from '#/main/app/content/tree/components/view/data-tree'

const DISPLAY_TREE = 'tree'

const DISPLAY_MODES = {
  [DISPLAY_TREE]: {
    icon: 'fa fa-fw fa-sitemap',
    label: trans('list_display_tree'),
    component: DataTree,
    options: {
      useCard: true, // it uses card representation for rendering data
      size: 'sm',
      orientation: 'row'
    }
  }
}

// reexport pagination constants here for retro compatibility
export const constants = {
  DISPLAY_MODES,
  DISPLAY_TREE
}
